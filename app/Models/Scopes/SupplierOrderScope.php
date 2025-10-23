<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SupplierOrderScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {

        $user = auth()->user();

        if (!$user) return;

        if ($user->role == 'Supplier') {
            $builder->whereHas('orderTours.tour', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
    }
}

