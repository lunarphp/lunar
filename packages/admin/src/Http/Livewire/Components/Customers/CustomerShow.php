<?php

namespace GetCandy\Hub\Http\Livewire\Components\Customers;

use Carbon\CarbonPeriod;
use GetCandy\DataTypes\Price;
use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Http\Livewire\Traits\WithCountries;
use GetCandy\Models\Address;
use GetCandy\Models\Currency;
use GetCandy\Models\Customer;
use GetCandy\Models\CustomerGroup;
use GetCandy\Models\Order;
use GetCandy\Models\OrderLine;
use GetCandy\Models\State;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerShow extends Component
{
    use Notifies, WithPagination, WithCountries;

    /**
     * The current customer in view.
     *
     * @var \GetCandy\Models\Customer
     */
    public Customer $customer;

    /**
     * An array of synced customer groups.
     *
     * @var array
     */
    public array $syncedGroups = [];

    /**
     * The ID of the user to remove.
     *
     * @var string|int
     */
    public $userIdToRemove = null;

    /**
     * The purchase history page.
     *
     * @var int
     */
    public $phPage = 1;

    /**
     * The order history page.
     *
     * @var int
     */
    public $ohPage = 1;

    /**
     * The users table page.
     *
     * @var int
     */
    public $uPage = 1;

    /**
     * The users search page.
     *
     * @var int
     */
    public $usPage = 1;

    /**
     * The search term for finding users.
     *
     * @var string
     */
    public $userSearchTerm = null;

    /**
     * The ID of the address to edit
     */
    public $addressIdToEdit = null;

    /**
     * The ID of the address to remove.
     *
     * @var string
     */
    public $addressToRemove = null;

    /**
     * The current address we want to edit.
     *
     * @var Address|null
     */
    public ?Address $address = null;

    /**
     * {@inheritDoc}
     */
    protected $queryString = [
        'phPage',
        'ohPage',
        'uPage',
        'usPage',
    ];

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            'syncedGroups'          => 'array',
            'customer.title'        => 'string|nullable',
            'customer.first_name'   => 'string|required',
            'customer.last_name'    => 'string|required',
            'customer.company_name' => 'nullable|string',
            'customer.vat_no'       => 'nullable|string',
            'address'               => 'nullable',
            'address.postcode' => 'required|string|max:255',
            'address.title' => 'nullable|string|max:255',
            'address.first_name' => 'nullable|string|max:255',
            'address.last_name' => 'nullable|string|max:255',
            'address.company_name' => 'nullable|string|max:255',
            'address.line_one' => 'nullable|string|max:255',
            'address.line_two' => 'nullable|string|max:255',
            'address.line_three' => 'nullable|string|max:255',
            'address.city' => 'nullable|string|max:255',
            'address.state' => 'nullable|string|max:255',
            'address.delivery_instructions' => 'nullable|string|max:255',
            'address.contact_email' => 'nullable|email|max:255',
            'address.contact_phone' => 'nullable|string|max:255',
            'address.country_id'   => 'required',
            'address.billing_default' => 'nullable',
            'address.shipping_default' => 'nullable',
        ];
    }

    /**
     * Called when the component is mounted.
     *
     * @return void
     */
    public function mount()
    {
        $this->address = new Address;
        $this->syncedGroups = $this->customer->customerGroups->pluck('id')->map(fn ($id) => (string) $id)->toArray();
    }

    /**
     * Save the customer record.
     *
     * @return void
     */
    public function save()
    {
        $this->validateOnly('customer');

        $this->customer->customerGroups()->sync(
            $this->syncedGroups
        );

        $this->customer->save();

        $this->notify(
            __('adminhub::notifications.customer.updated')
        );
    }

    /**
     * Handler for when address to update changes.
     *
     * @param string $val
     * @return void
     */
    public function updatedAddressIdToEdit($val)
    {
        if ($val) {
            return $this->setEditableAddress($val);
        }

        $this->address = new Address;
    }

    /**
     * Save the address
     *
     * @return void
     */
    public function saveAddress()
    {
        $this->validateOnly('address');
        $this->address->save();
        $this->addressIdToEdit = null;
        $this->address = new Address;
        $this->notify(
            __('adminhub::notifications.customers.address_updated')
        );
    }

    /**
     * Remove an address.
     *
     * @return void
     */
    public function removeAddress()
    {
        Address::find($this->addressToRemove)->delete();
        $this->addressToRemove = null;

        $this->notify(
            __('adminhub::notifications.customers.address_removed')
        );
    }


    /**
     * Return the computed customer groups.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCustomerGroupsProperty()
    {
        return CustomerGroup::get();
    }

    /**
     * Return the paginated customer orders.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getOrdersProperty()
    {
        return $this->customer->orders()->orderBy('placed_at', 'desc')->paginate(
            perPage: 10,
            pageName: 'ohPage'
        );
    }

    /**
     * Return the users for the customer.
     *
     * @return void
     */
    public function getUsersProperty()
    {
        return $this->customer->users()->paginate(
            perPage: 10,
            pageName: 'uPage',
        );
    }

    /**
     * Remove a user from a customer.
     *
     * @return void
     */
    public function removeUser()
    {
        $this->customer->users()->detach($this->userIdToRemove);

        $this->userIdToRemove = null;
        $this->notify(
            __('adminhub::notifications.customers.user_removed')
        );
    }

    /**
     * Return the paginated addresses.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAddressesProperty()
    {
        return $this->customer->addresses()->paginate(10);
    }

    /**
     * Set the editable address.
     */
    public function setEditableAddress($addressId)
    {
        $this->address = $this->addresses->first(
            fn($address) => $address->id == $addressId
        );
    }

    /**
     * Send password reset reminder.
     *
     * @param  string|int  $userId
     * @return void
     */
    public function sendPasswordReset($userId)
    {
        $user = $this->users->first(fn ($user) => $user->id == $userId);

        if (! $user) {
            $this->notify(
                __('adminhub::notifications.customers.reset_failed'),
                level: 'error'
            );

            return;
        }
        $status = Password::sendResetLink([
            'email' => $user->email,
        ]);

        $this->notify(
            __($status),
            level: $status != Password::RESET_LINK_SENT ? 'error' : 'success'
        );
    }

    /**
     * Return states for the shipping address.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getStatesProperty()
    {
        if (! $this->address || !$this->address?->country_id) {
            return collect();
        }

        return State::whereCountryId($this->address->country_id)->get();
    }

    /**
     * Return the order count for the customer.
     *
     * @return int
     */
    public function getOrdersCountProperty()
    {
        return $this->customer->orders()->count();
    }


    /**
     * Return the average spend for the customer.
     *
     * @return \GetCandy\DataTypes\Price
     */
    public function getAvgSpendProperty()
    {
        $avg = (int) round($this->customer->orders()->average(
            DB::RAW('sub_total * exchange_rate')
        ));

        return new Price($avg, Currency::getDefault());
    }

    /**
     * Return the average spend for the customer.
     *
     * @return \GetCandy\DataTypes\Price
     */
    public function getTotalSpendProperty()
    {
        $avg = (int) round($this->customer->orders()->sum(
            DB::RAW('sub_total * exchange_rate')
        ));

        return new Price($avg, Currency::getDefault());
    }

    /**
     * Return the spending chart data.
     *
     * @return \GetCandy\Models\Collection
     */
    public function getSpendingChartProperty()
    {
        $start = now()->subYear()->startOfDay();
        $end = now()->endOfDay();
        $defaultCurrency = Currency::getDefault();

        $thisPeriod = $this->customer->orders()->select(
            DB::RAW('SUM(sub_total) as sub_total'),
            db_date('placed_at', '%Y-%m', 'format_date')
        )->whereNotNull('placed_at')
        ->whereBetween('placed_at', [
            $start,
            $end,
        ])->groupBy('format_date')->get();

        $previousPeriod = $this->customer->orders()->select(
            DB::RAW('SUM(sub_total) as sub_total'),
            db_date('placed_at', '%Y-%m', 'format_date')
        )->whereNotNull('placed_at')
        ->whereBetween('placed_at', [
            $start->clone()->subYear(),
            $end->clone()->subYear(),
        ])->groupBy('format_date')->get();

        $period = CarbonPeriod::create($start, '1 month', $end);

        $thisPeriodMonths = collect();
        $previousPeriodMonths = collect();
        $months = collect();

        foreach ($period as $datetime) {
            $months->push($datetime->toDateTimeString());
            // Do we have some totals for this month?
            if ($totals = $thisPeriod->first(fn ($p) => $p->format_date == $datetime->format('Y-m'))) {
                $thisPeriodMonths->push($totals->sub_total->decimal);
            } else {
                $thisPeriodMonths->push(0);
            }
            if ($prevTotals = $previousPeriod->first(fn ($p) => $p->format_date == $datetime->format('Y-m'))) {
                $previousPeriodMonths->push($prevTotals->sub_total->decimal);
            } else {
                $previousPeriodMonths->push(0);
            }
        }

        return collect([
            'chart' => [
                'type'    => 'area',
                'toolbar' => [
                    'show' => false,
                ],
                'height' => '100%',
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'fill' => [
                'type'     => 'gradient',
                'gradient' => [
                    'shadeIntensity' => 1,
                    'opacityFrom'    => 0.45,
                    'opacityTo'      => 0.05,
                    'stops'          => [50, 100, 100, 100],
                ],
            ],
            'series' => [
                [
                    'name' => 'This Period',
                    'data' => $thisPeriodMonths->toArray(),
                ],
                [
                    'name' => 'Previous Period',
                    'data' => $previousPeriodMonths->toArray(),
                ],
            ],
            'xaxis' => [
                'type'       => 'datetime',
                'categories' => $months->toArray(),
            ],
            'yaxis' => [
                'title' => [
                    'text' => "Spending {$defaultCurrency->code}",
                ],
            ],
            'tooltip' => [
                'x' => [
                    'format' => 'dd MMM yyyy',
                ],
            ],
        ]);
    }

    /**
     * Return the purchase history.
     *
     * @return void
     */
    public function getPurchaseHistoryProperty()
    {
        $ordersTable = (new Order)->getTable();
        $orderLinesTable = (new OrderLine)->getTable();

        $column = db_date('placed_at', '%Y-%m-%d');

        return OrderLine::select(
            DB::RAW('COUNT(*) as order_count'),
            DB::RAW('SUM(quantity) as quantity'),
            DB::RAW("SUM({$orderLinesTable}.sub_total) as sub_total"),
            'description',
            'identifier',
            DB::RAW("MAX({$column}) as last_ordered")
        )->join($ordersTable, "{$ordersTable}.id", '=', "{$orderLinesTable}.order_id")
        ->whereIn(
            'order_id', $this->customer->orders()->pluck('id')
        )->whereType('physical')->groupBy(['identifier', 'description'])->paginate(
            perPage: 10,
            pageName: 'phPage'
        );
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.customers.show')
            ->layout('adminhub::layouts.base');
    }
}
