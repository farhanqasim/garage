<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarWashWorker extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'mobile',
        'additional_mobiles',
        'father_name',
        'father_mobile',
        'father_additional_mobiles',
        'location',
        'commission',
        'id_card_front',
        'id_card_back',
        'father_card_front',
        'father_card_back',
        'status',
        'bank_account_id',
        'bank_name',
        'bank_account_title',
        'bank_account_number',
        'bank_iban',
    ];

    /**
     * Worker's linked bank account (from Bank module) - for commission credits and history
     */
    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Worker's cash account – commission paid by cash is credited here
     */
    public function workerCashAccount()
    {
        return $this->hasOne(WorkerCashAccount::class, 'worker_id');
    }

    protected $casts = [
        'additional_mobiles' => 'array',
        'father_additional_mobiles' => 'array',
        'commission' => 'integer',
        'status' => 'boolean',
    ];

    /**
     * Get the branch that owns this worker
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get jobs assigned to this worker
     */
    public function jobs()
    {
        return $this->hasMany(CarWashJob::class, 'worker_id');
    }

    /**
     * Get completed jobs count
     */
    public function completedJobsCount()
    {
        return $this->jobs()->where('status', 'completed')->count();
    }

    /**
     * Get total earnings
     */
    public function totalEarnings()
    {
        return $this->jobs()->where('status', 'completed')->sum('price');
    }
}
