<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ClinicContext;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;

class SetClinicContext
{
    protected $clinicContext;

    public function __construct(ClinicContext $clinicContext)
    {
        $this->clinicContext = $clinicContext;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        if (!$user) {
            return $next($request);
        }

        // 1. Check if header exists
        $clinicIdHash = $request->header('X-Clinic-ID');

        if ($clinicIdHash) {
            // Special Case: 'all' for Super Admin Global View
            if ($clinicIdHash === 'all') {
                // Allow Super Admins OR Users with 'admin' role OR permission
                if ($user->is_superadmin || $user->hasRole('admin') || $user->can('clinic_locations_view_all')) {
                     $this->clinicContext->setClinic('all');
                     return $next($request);
                } else {
                     abort(403, 'Global view is restricted to Super Admins.');
                }
            }

            // Standard Case: Specific Clinic ID
            $decoded = Hashids::decode($clinicIdHash);
            $clinicId = !empty($decoded) ? $decoded[0] : null;

            if ($clinicId) {
                // 2. Verify user belongs to clinic
                if ($user->canAccessClinic((int)$clinicId)) {
                    // 3. Set context
                    $this->clinicContext->setClinic((int)$clinicId);

                    // 4. Inject Role for Contextual Permissions
                    $role = $user->currentRole();
                    if ($role) {
                        // Override global roles with the specific clinic role
                        $user->setRelation('roles', collect([$role]));
                        
                        // Also clear permission cache/relation if loaded, though setRelation 'roles' 
                        // usually suffices for Spatie's hasRole/hasPermission checks if they rely on relations.
                        // Spatie caches permissions in memory on the model instance. 
                        $user->unsetRelation('permissions');
                    } else {
                        // If no specific role assigned for this clinic, user has NO roles in this context
                        // (unless super admin, but logic here overrides standard roles)
                         $user->setRelation('roles', collect([]));
                         $user->unsetRelation('permissions');
                    }
                } else {
                     // 5. Access Denied? Try Auto-Switch!
                     // If user is trying to access a restricted clinic, switch them to their first valid clinic.
                     $firstClinic = $user->clinics()->orderBy('id', 'asc')->first();

                     if ($firstClinic) {
                         $this->clinicContext->setClinic((int)$firstClinic->id);
                         
                         // Re-apply role logic for the NEW clinic
                         $role = $user->currentRole(); // user model now scoped? No, context updated but not role helper yet?
                         // The helper 'currentRole' likely reads from context service.
                         // Since we updated context above, currentRole() should now return role for new clinic.
                         
                         if ($role) {
                            $user->setRelation('roles', collect([$role]));
                            $user->unsetRelation('permissions');
                         } else {
                             $user->setRelation('roles', collect([]));
                             $user->unsetRelation('permissions');
                         }
                     } else {
                         // User has no valid clinics at all
                         abort(403, 'Unauthorized access to this clinic.');
                     }
                }
            } else {
                // Invalid HashID provided in header
                abort(400, 'Invalid Clinic ID.');
            }
        } else {
            // 4. Fallback: Default clinic
            // Only set default clinic if NOT Super Admin. Super Admins see all by default if no header.
            if (!$user->is_superadmin && $user->default_clinic_id) {
                 $this->clinicContext->setClinic((int)$user->default_clinic_id);
            }
        }

        return $next($request);
    }
}
