<div class="p-4 bg-white rounded-lg shadow min-h-96">
  <div class="md:flex items-center justify-between">
    <div>
      <h3 class="text-lg font-semibold text-gray-900">
        {{ __('adminhub::catalogue.customer.dashboard.sales_performance') }}
      </h3>
    </div>
    <form wire:submit.prevent="update" class="flex items-center space-x-2">
        <x-hub::input.datepicker wire:model="range" :options="['mode' => 'range', 'maxRange' => 30 ]" class="!w-[300px]" />
        <button class="mt-2">
          <x-hub::icon ref="refresh" class="w-4 text-gray-400 hover:text-gray-900"/>
        </button>
    </form>
  </div>

    <div class="h-80">
      <div
        style="width: 100%; height: 100%;"
        x-data='{
            init() {
                var el = document.querySelector("#sales-performance");
                var options = @json($this->graphData);

                options.yaxis.labels = {
                    formatter: function (value) {
                        return new Intl.NumberFormat("en-gb", {
                            style: "currency",
                            currency: "{{ $this->defaultCurrency->code }}",
                            maximumFractionDigits:0
                        }).format(value)
                    }
                }

                var chart = new ApexCharts(el, options);
                chart.render();
            }
        }'
      >
        <div id="sales-performance"></div>
      </div>
    </div>
</div>
