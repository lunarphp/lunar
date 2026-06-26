<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Invoice</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        * {
            font-size:12px;
            font-family: 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }

        .lines {
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .discount-seperator {
            color:#ccc;
        }
        .lines-heading {
            text-align: left;
            background-color: #ededed;
        }

        .lines-heading th {
            padding: 5px 10px;
            border: 1px solid #ccc;
        }

        .lines-body td {
            padding: 5px 10px;
            border: 1px solid #ededed;
        }

        .lines-footer {
            border-top:5px solid #f5f5f5;
            text-align:right;
        }

        .lines-footer td {
            padding: 10px;
            border: 1px solid #ededed;
        }

        .summary {
            margin-bottom: 40px;
        }

        .summary td {
            padding: 5px 10px;
        }

        .info {
            color:#0099e5;
        }

        .summary .total td {
            border-top: 1px solid #ccc;
        }


    </style>
</head>

<body>
    <div class="content">
        <div class="invoice-box">

            <table cellpadding="0" cellspacing="0" width="100%">
                <tr class="top">
                    <td>
                        <table width="100%">
                            <tr>
                                <td class="title" width="50%">
                                    <h1>{{ config('app.name') }}</h1>
                                </td>
                                <td align="right" width="50%">
                                    Invoice: {{ @$record->reference }} <br>
                                    Created: {{ $record->placed_at }}<br>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <table cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td>
                        <table width="100%">
                            <tr>
                                <td align="left" width="33%">
                                    <h3>Billing</h3>
                                    {{ $record->billingAddress->fullName }}<br>
                                    @if($record->billingAddress->company_name)
                                      {{ $record->billingAddress->company_name }}<br>
                                    @endif
                                    @if($record->billingAddress->tax_identifier)
                                      {{ $record->billingAddress->tax_identifier }}<br>
                                    @endif
                                    {{ $record->billingAddress->line_one }}
                                    @if($record->billingAddress->line_two)
                                      <br>{{ $record->billingAddress->line_two }}<br>
                                    @endif
                                    @if($record->billingAddress->line_three)
                                      <br>{{ $record->billingAddress->line_three }}<br>
                                    @endif
                                    {{ $record->billingAddress->city }}<br>
                                    {{ $record->billingAddress->state }}<br>
                                    {{ $record->billingAddress->postcode }}<br>
                                    {{ $record->billingAddress->country->name }}<br>
                                    @if($record->customer?->tax_identifier)
                                        <p>Tax Identifier: {{ $record->customer?->tax_identifier }}</p>
                                    @endif
                                </td>

                                @if ($record->shippingAddress)
                                <td align="left" width="33%">
                                    <h3>Shipping</h3>
                                    {{ $record->shippingAddress->fullName }}<br>
                                    @if($record->shippingAddress->company_name)
                                      {{ $record->shippingAddress->company_name }}<br>
                                    @endif
                                    @if($record->shippingAddress->tax_identifier)
                                      {{ $record->shippingAddress->tax_identifier }}<br>
                                    @endif
                                    {{ $record->shippingAddress->line_one }}
                                    @if($record->shippingAddress->line_two)
                                      <br>{{ $record->shippingAddress->line_two }}<br>
                                    @endif
                                    @if($record->shippingAddress->line_three)
                                      <br>{{ $record->shippingAddress->line_three }}<br>
                                    @endif
                                    {{ $record->shippingAddress->city }}<br>
                                    {{ $record->shippingAddress->state }}<br>
                                    {{ $record->shippingAddress->postcode }}<br>
                                    {{ $record->shippingAddress->country->name }}<br>
                                </td>
                                @endif

                                <td align="right" width="33%">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <table cellpadding="0" cellspacing="0" width="100%" class="lines">
                <thead class="lines-heading">
                    <tr width="100%">
                        <th width="35%">
                            Product
                        </th>
                        <th width="28%">
                            SKU
                        </th>
                        <th width="10%">
                            Qty
                        </th>
                        <th width="15%">
                            Unit Price
                        </th>
                        <th width="15%">
                            Discount
                        </th>
                        <th width="15%">
                            Tax Rate
                        </th>
                        <th width="15%">
                            Tax Amount
                        </th>
                        <th width="12%">
                            Line Total
                        </th>
                    </tr>
                </thead>
                <tbody class="lines-body">
                  @foreach($record->fulfillableLines as $line)
                    <tr>
                      <td>
                        {{ $line->description }} <br>
                        {{ $line->option }}
                      </td>
                      <td>
                        {{ $line->identifier }}
                      </td>
                      <td>
                        {{ $line->quantity }}
                      </td>
                      <td>
                        {{ $line->format('unit_price') }}
                      </td>
                      <td>
                        {{ $line->format('discount_total') }}
                      </td>
                      <td>
                        {{ $line->tax_breakdown->amounts->sum('percentage') }}%
                      </td>
                      <td>
                        {{ $line->format('tax_total') }}
                      </td>

                      <td>
                        {{ $line->format('sub_total') }}
                      </td>
                    </tr>
                  @endforeach
                </tbody>
                <tfoot class="lines-footer">
                    <tr>
                        <td colspan="5"></td>
                        <td colspan="2"><strong>Sub Total</strong></td>
                        <td>{{ $record->format('sub_total') }}</td>
                    </tr>
                    @foreach($record->shippingLines as $line)
                      <tr>
                        <td colspan="4"></td>
                        <td colspan="3">
                          <strong>Shipping</strong><br>
                          <small>{{ strip_tags($line->description) }}</small>
                        </td>
                        <td>{{ $line->format('sub_total') }}</td>
                      </tr>
                    @endforeach
                    <tr>
                        <td colspan="5"></td>
                        <td colspan="2"><strong>Tax</strong></td>
                        <td>{{ $record->format('tax_total') }}</td>
                    </tr>
                    <tr>
                        <td colspan="5"></td>
                        <td colspan="2"><strong>Total</strong></td>
                        <td>{{ $record->format('total') }}</td>
                    </tr>
                </tfoot>
            </table>

            @if($record->notes)
            <p><strong>Order Notes</strong><br>
            {{ $record->notes }}</p>
            <br>
            @endif
        </div>
    </div>
</body>
</html>
