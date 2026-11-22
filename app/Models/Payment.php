<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    public const TUJUAN_SEWA_AWAL = 'sewa_awal';
    public const TUJUAN_PERPANJANGAN = 'perpanjangan';

    public const SEWA_HARIAN = 'harian';
    public const SEWA_BULANAN = 'bulanan';

    /**
     * Atribut yang dapat diisi secara mass-assignment.
     *
     * @var list<string>
     */
    protected $fillable = [
        'rental_id',
        'tujuan',
        'tipe_sewa',
        'nominal',
        'bukti_bayar',
    ];

    /**
     * Konversi tipe data atribut otomatis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'nominal' => 'integer',
    ];

    /**
     * Relasi: Pembayaran merujuk ke satu data penyewaan.
     */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }
}