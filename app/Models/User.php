<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_PENYEWA = 'penyewa';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Atribut yang dapat diisi secara mass-assignment.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nik',
        'nama',
        'no_telp',
        'email',
        'password',
        'role',
        'status',
    ];

    /**
     * Atribut yang disembunyikan saat serialisasi.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Konversi tipe data atribut otomatis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    /**
     * Relasi: Satu user (penyewa) dapat memiliki banyak data sewa.
     */
    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }
}