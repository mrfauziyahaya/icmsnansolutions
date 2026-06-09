<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Invoice;

class Client extends Model
{
    use HasFactory;

    protected $primaryKey = 'client_id';
    public $incrementing = true;

    protected $fillable = [
        'name',
        'phone',
        'category',
        'plate',
        'vehicle_model',
        'address1',
        'insurance_company',
        'nettpremium',
        'premium',
        'road_tax_price',
        'expiry_date',
        'renewal_date',
        'status',
        'address2',
        'city',
        'state',
        'postcode',
        'mykad_companyno',
        'inception_date',
        'reminder_date',
        'document_name',
        'document_path',
        'document_type',
        'document_uploaded_at'
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'client_id', 'client_id')->latest('issued_at');
    }

    protected $casts = [
        'nettpremium' => 'decimal:2',
        'premium' => 'decimal:2',
        'road_tax_price' => 'decimal:2',
        'expiry_date' => 'date',
        'renewal_date' => 'date',
        'inception_date' => 'date',
        'reminder_date' => 'date',
        'document_uploaded_at' => 'datetime'
    ];
}
