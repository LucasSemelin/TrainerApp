<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientTrainer extends Model
{
    /** @use HasFactory<\Database\Factories\ClientTrainerFactory> */
    use HasFactory;

    protected $table = 'client_trainer';
}
