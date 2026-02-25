<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;

class S3TestController extends ApiBaseController
{
    public function testConnection(Request $request)
    {
        try {
            // Check if S3 is configured
            $s3Config = config('filesystems.disks.s3');
            // dd($s3Config);
            
            if (empty($s3Config) || $s3Config['driver'] !== 's3') {
                return ApiResponse::make('Error', [
                    'message' => 'S3 is not configured in your application.'
                ], 500);
            }
            
            // Get test mode from query parameters or default to 'basic'
            $testMode = $request->query('mode', 'basic');
            $results = [
                'timestamp' => now()->toDateTimeString(),
                'test_mode' => $testMode,
                'message' => 'S3 configuration test',
                'config_status' => [
                    'driver' => $s3Config['driver'] ?? 'Not configured',
                    'key' => !empty($s3Config['key']) ? 'Configured' : 'Missing',
                    'secret' => !empty($s3Config['secret']) ? 'Configured' : 'Missing',
                    'region' => $s3Config['region'] ?? 'Not configured',
                    'bucket' => $s3Config['bucket'] ?? 'Not configured',
                    'url' => $s3Config['url'] ?? 'Not configured',
                    'endpoint' => $s3Config['endpoint'] ?? 'Not configured',
                    'use_path_style' => $s3Config['use_path_style_endpoint'] ?? 'Not configured',
                ]
            ];
            
            // Check if essential configuration is present
            if (!$s3Config['key'] || !$s3Config['secret'] || !$s3Config['region'] || !$s3Config['bucket']) {
                $results['status'] = 'Error';
                $results['message'] = 'S3 is not properly configured. Please check your .env file.';
                return ApiResponse::make('Error', $results, 400);
            }
            
            // If mode is set to 'connection', attempt to list files
            if ($testMode === 'connection' || $testMode === 'full') {
                try {
                    // List files in the bucket to test connection
                    $files = Storage::disk('s3')->allFiles('/');
                    $results['connection_test'] = [
                        'status' => 'Success',
                        'message' => 'Successfully connected to S3',
                        'file_count' => count($files),
                        'files' => $testMode === 'full' ? $files : (count($files) > 5 ? array_slice($files, 0, 5) : $files),
                    ];
                } catch (\Exception $e) {
                    $results['connection_test'] = [
                        'status' => 'Failed',
                        'message' => 'Failed to connect to S3: ' . $e->getMessage()
                    ];
                }
            }
            
            // Return success with detailed information
            return ApiResponse::make('Success', $results);
        } catch (\Exception $e) {
            return ApiResponse::make('Error', [
                'message' => 'Failed to test S3 configuration: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function testUpload(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'file' => 'required|file|max:10240', // 10MB max
            ]);
            
            if (!$request->hasFile('file')) {
                return ApiResponse::make('Error', ['message' => 'No file uploaded'], 400);
            }
            
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // Upload to S3
            $path = Storage::disk('s3')->putFileAs(
                'uploads/tests', 
                $file, 
                $fileName, 
                'public'
            );
            
            // Manually construct the URL for DigitalOcean Spaces
            $bucket = config('filesystems.disks.s3.bucket');
            $endpoint = config('filesystems.disks.s3.endpoint');
            $customUrl = config('filesystems.disks.s3.url');
            
            if ($customUrl) {
                $url = rtrim($customUrl, '/') . '/' . ltrim($path, '/');
            } elseif ($endpoint) {
                // For DigitalOcean Spaces, construct URL with endpoint
                $url = rtrim($endpoint, '/') . '/' . ltrim($path, '/');
            } else {
                // Fallback to standard S3 URL
                $region = config('filesystems.disks.s3.region');
                $url = "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
            }
            
            return ApiResponse::make('Success', [
                'message' => 'File uploaded successfully',
                'path' => $path,
                'url' => $url
            ]);
        } catch (\Exception $e) {
            return ApiResponse::make('Error', [
                'message' => 'Failed to upload file: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function testDelete(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'path' => 'required|string',
            ]);
            
            $path = $request->path;
            
            // Check if file exists
            if (!Storage::disk('s3')->exists($path)) {
                return ApiResponse::make('Error', [
                    'message' => 'File not found at path: ' . $path
                ], 404);
            }
            
            // Delete from S3
            Storage::disk('s3')->delete($path);
            
            return ApiResponse::make('Success', [
                'message' => 'File deleted successfully',
                'path' => $path
            ]);
        } catch (\Exception $e) {
            return ApiResponse::make('Error', [
                'message' => 'Failed to delete file: ' . $e->getMessage()
            ], 500);
        }
    }
}
