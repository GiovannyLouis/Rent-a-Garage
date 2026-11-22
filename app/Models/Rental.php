<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rental extends Model
{
    use HasFactory;

    public const KENDARAAN_SEPEDA = 'sepeda';
    public const KENDARAAN_SEPEDA_MOTOR = 'sepeda_motor';
    public const KENDARAAN_MOBIL = 'mobil';
    public const KENDARAAN_MOBIL_LISTRIK = 'mobil_listrik';

    /**
     * Atribut yang dapat diisi secara mass-assignment.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'garage_id',
        'tipe_kendaraan',
        'plat_nomor',
        'merek_tipe',
        'kapasitas_baterai',
        'sisa_baterai',
        'jumlah_hari',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    /**
     * Konversi tipe data atribut otomatis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'kapasitas_baterai' => 'float',
        'sisa_baterai' => 'float',
        'jumlah_hari' => 'integer',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    /**
     * Relasi: Penyewaan dimiliki oleh satu user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Penyewaan menggunakan satu garasi.
     */
    public function garage(): BelongsTo
    {
        return $this->belongsTo(Garage::class);
    }

    /**
     * Relasi: Satu penyewaan dapat memiliki banyak catatan pembayaran (sewa awal & perpanjangan).
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}