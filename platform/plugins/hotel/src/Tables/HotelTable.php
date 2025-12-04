<?php

namespace Botble\Hotel\Tables;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Hotel\Models\Hotel;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Base\Facades\Html;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\ImageColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;

class HotelTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Hotel::class)
            ->addActions([
                EditAction::make()->route('hotel.edit'),
                DeleteAction::make()->route('hotel.destroy'),
            ]);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'name',
                'image',
                'address',
                'phone',
                'email',
                'created_at',
                'status',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            ImageColumn::make(),
            FormattedColumn::make('name')
                ->title(trans('core/base::tables.name'))
                ->alignLeft()
                ->renderUsing(function (FormattedColumn $column) {
                    $hotel = $column->getItem();
                    $url = route('hotel.edit', $hotel->id);
                    $badgeHtml = Html::tag('span', $hotel->name, [
                        'class' => 'badge bg-info text-info-fg',
                        'style' => 'font-size: 1.1em; font-weight: bold; padding: 0.5em 1em;'
                    ]);
                    return Html::link($url, $badgeHtml, [], null, false);
                }),
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('hotel.create'), 'hotel.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('hotel.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'name' => [
                'title' => trans('core/base::tables.name'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => BaseStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', BaseStatusEnum::values()),
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
        ];
    }
}
