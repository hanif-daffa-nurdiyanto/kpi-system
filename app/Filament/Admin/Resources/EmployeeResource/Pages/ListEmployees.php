<?php

namespace App\Filament\Admin\Resources\EmployeeResource\Pages;

use App\Filament\Admin\Resources\EmployeeResource as ResourcesEmployeeResource;
use App\Filament\Resources\EmployeeResource;
use App\Models\Department;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class ListEmployees extends ListRecords
{
    protected static string $resource = ResourcesEmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('department_id')
                ->label('Department')
                ->relationship('department', 'name')
                ->searchable()
                ->preload(),
        ];
    }

    public function getTabs(): array
    {
        return [
            Tab::make('All')
                ->label('All Employees')
                ->query(fn(Builder $query) => $query),
            ...$this->getDepartmentTabs(),
        ];
    }

    protected function getDepartmentTabs(): array
    {
        return Department::query()
            ->get()
            ->map(function (Department $department) {
                return Tab::make($department->name)
                    ->label($department->name)
                    ->query(fn(Builder $query) => $query->where('department_id', $department->id));
            })
            ->toArray();
    }
}
