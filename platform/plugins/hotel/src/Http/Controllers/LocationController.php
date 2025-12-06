<?php

namespace Botble\Hotel\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Hotel\Forms\LocationForm;
use Botble\Hotel\Http\Requests\LocationRequest;
use Botble\Hotel\Models\Location;
use Botble\Hotel\Tables\LocationTable;

class LocationController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans('plugins/hotel::location.name'))
            ->add(trans('plugins/hotel::location.locations'), route('location.index'));
    }

    public function index(LocationTable $table)
    {
        $this->pageTitle(trans('plugins/hotel::location.locations'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/hotel::location.create'));

        return LocationForm::create()->renderForm();
    }

    public function store(LocationRequest $request)
    {
        $form = LocationForm::create();
        $form->save();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('location.index'))
            ->setNextUrl(route('location.edit', $form->getModel()->getKey()))
            ->withCreatedSuccessMessage();
    }

    public function edit(Location $location)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $location->name]));

        return LocationForm::createFromModel($location)->renderForm();
    }

    public function update(Location $location, LocationRequest $request)
    {
        LocationForm::createFromModel($location)
            ->setRequest($request)
            ->save();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('location.index'))
            ->withUpdatedSuccessMessage();
    }

    public function destroy(Location $location)
    {
        return DeleteResourceAction::make($location);
    }
}
