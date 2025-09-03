<?php

namespace App\Filament\Widgets;

use Filament\Forms\Form;
use Filament\Widgets\Widget;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;

class DateRangeFilter extends Widget implements hasForms
{
    use InteractsWithForms;
    protected static string $view = 'filament.admin.widgets.date-range-filter';
    protected static ?int $sort = 1;
    public ?string $startDate = null;
    public ?string $endDate = null;


    public function form(Form $form)
    {
        return $form
            ->schema([
                Grid::make(1)
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Start Date')
                            ->columnSpan(1)
                            ->live()
                            ->afterStateUpdated( function () {
                                $this->dispatch('startDate', $this->startDate);
                            }),

                        DatePicker::make('endDate')
                            ->label('End Date')
                            ->columnSpan(1)
                            ->live()
                            ->afterStateUpdated(function () {
                                $this->dispatch('endDate', $this->endDate);
                            }),
                    ]),
            ]);
    }
}

