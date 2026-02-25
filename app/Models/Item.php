<?php
namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends BaseModel
{
    use HasFactory;

    protected $table = 'items';

    protected $default = ['id', 'name', 'sku', 'available_quantity', 'is_sellable', 'is_purchasable'];

    protected $hidden = ['id', 'category_id', 'company_id', 'manufacturer_id', 'brand_id', 'supplier_id'];

    protected $appends = ['xid', 'x_category_id', 'x_company_id', 'x_manufacturer_id', 'x_brand_id', 'x_supplier_id'];

    protected $filterable = ['name', 'category_id', 'manufacturer_id', 'brand_id', 'is_sellable', 'is_purchasable', 'type'];

    protected $fillable = [
        'name',
        'category_id',
        'unit',
        'description',
        'available_quantity',
        'alert_quantity',
        'company_id',
        'sku',
        'type',
        'item_length',
        'item_width',
        'item_height',
        'dimension_unit',
        'weight',
        'weight_unit',
        'manufacturer_id',
        'brand_id',
        // Sales information
        'is_sellable',
        'sale_price',
        'sale_description',
        // Purchase information
        'is_purchasable',
        'cost_price',
        'purchase_description',
        'supplier_id',
        'returnable',
    ];

    protected $hashableGetterFunctions = [
        'getXCategoryIdAttribute' => 'category_id',
        'getXCompanyIdAttribute' => 'company_id',
        'getXManufacturerIdAttribute' => 'manufacturer_id',
        'getXBrandIdAttribute' => 'brand_id',
        'getXSupplierIdAttribute' => 'supplier_id',
    ];

    protected $casts = [
        'category_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'manufacturer_id' => Hash::class . ':hash',
        'brand_id' => Hash::class . ':hash',
        'supplier_id' => Hash::class . ':hash',
        'is_sellable' => 'boolean',
        'is_purchasable' => 'boolean',
        'returnable' => 'boolean',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'alert_quantity' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    public function manufacturer()
    {
        return $this->belongsTo(ItemManufacture::class, 'manufacturer_id');
    }

    public function brand()
    {
        return $this->belongsTo(ItemBrand::class, 'brand_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function medicine()
    {
        return $this->hasOne(Medicine::class, 'item_id');
    }
}
