<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SupplierScope implements Scope
{
    protected string $column;

    /**
     * You can specify which column represents the supplier (default: user_id)
     */
    public function __construct(string $column = 'user_id')
    {
        $this->column = $column;
    }

    public function apply(Builder $builder, Model $model)
    {
        $user = auth()->user();

        if (!$user) return;
        // Apply filter only for suppliers

        if ($user->role == 'Supplier') {
            
            $builder->where($this->column, $user->id);
        }
    }
}
