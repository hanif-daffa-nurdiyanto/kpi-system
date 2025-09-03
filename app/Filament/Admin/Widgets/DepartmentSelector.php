<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Department;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DepartmentSelector extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.admin.widgets.department-selector';

    public ?string $departmentId = null;

    protected function getFormSchema(): array
    {
        return [
            Select::make('departmentId')
                ->label('Filter by Department')
                ->options(Department::pluck('name', 'id'))
                ->placeholder('All Departments')
                ->live()
                ->afterStateUpdated(function () {
                    $this->dispatch('departmentSelected', departmentId: $this->departmentId);
                })
        ];
    }
}
