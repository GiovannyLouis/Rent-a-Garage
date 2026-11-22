<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Garage extends Model
{
    use HasFactory;

    public const TIPE_SEPEDA = 'sepeda';
    public const TIPE_MOBIL = 'mobil';
    public const TIPE_MOBIL_LISTRIK = 'mobil_listrik';

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_USED = 'used';

    /**
     * Atribut yang dapat diisi secara mass-assignment.
     *
     * @var list<string>
     */
    protected $fillable = [
        'kode',
        'tipe_garasi',
        'harga_harian',
        'harga_kwh',
        'status',
    ];

    /**
     * Konversi tipe data atribut otomatis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'harga_harian' => 'integer',
        'harga_kwh' => 'integer',
    ];

    /**
     * Relasi: Satu garasi dapat memiliki banyak riwayat penyewaan.
     */
    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }
}