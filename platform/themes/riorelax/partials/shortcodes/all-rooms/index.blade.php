<section class="services-area pt-20 pb-40">
    <div class="row mb-30">
        <div class="col-lg-12">
            <form method="GET" action="{{ url()->current() }}" class="filter-form">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-3">
                        <label for="location_id" class="form-label">{{ __('Filter by Location') }}</label>
                        <select name="location_id" id="location_id" class="form-select">
                            <option value="">{{ __('All Locations') }}</option>
                            @foreach($locations as $id => $name)
                                <option value="{{ $id }}" {{ request()->query('location_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="hotel_id" class="form-label">{{ __('Filter by Hotel') }}</label>
                        <select name="hotel_id" id="hotel_id" class="form-select">
                            <option value="">{{ __('All Hotels') }}</option>
                            @foreach($hotels as $id => $name)
                                <option value="{{ $id }}" {{ request()->query('hotel_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <button type="submit" class="btn ss-btn w-100">{{ __('Apply Filters') }}</button>
                        @if(request()->has('location_id') || request()->has('hotel_id'))
                            <a href="{{ url()->current() }}" class="btn btn-secondary w-100 mt-2">{{ __('Clear Filters') }}</a>
                        @endif
                    </div>
                </div>

                @foreach(request()->except(['location_id', 'hotel_id']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
            </form>
        </div>
    </div>

    <h3 class="mb-20">{{ __(':count rooms available', ['count' => $rooms->total()]) }}</h3>

    @if ($rooms->isNotEmpty())
        <div class="row">
            @foreach ($rooms as $room)
                <div class="col-md-6">
                    {!! Theme::partial('rooms.item', compact('room', 'startDate', 'endDate', 'nights', 'adults')) !!}
                </div>
            @endforeach
        </div>
        @if ($rooms instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <div class="text-center mt-30">
                {!! $rooms->withQueryString()->links(Theme::getThemeNamespace('partials.pagination')) !!}
            </div>
        @endif
    @endif
</section>

