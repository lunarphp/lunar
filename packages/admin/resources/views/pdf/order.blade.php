<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Invoice</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-size:12px;
            font-family:'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
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
                                    Invoice: {{ @$order->reference }} <br>
                                    Created: {{ $order->placed_at }}<br>
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
                                    {{ $order->billingAddress->fullName }}<br>
                                    @if($order->billingAddress->company_name)
                                      {{ $order->billingAddress->company_name }}<br>
                                    @endif
                                    {{ $order->billingAddress->line_one }}
                                    @if($order->billingAddress->line_two)
                                      <br>{{ $order->billingAddress->line_two }}<br>
                                    @endif
                                    @if($order->billingAddress->line_three)
                                      <br>{{ $order->billingAddress->line_three }}<br>
                                    @endif
                                    {{ $order->billingAddress->city }}<br>
                                    {{ $order->billingAddress->state }}<br>
                                    {{ $order->billingAddress->postcode }}<br>
                                    {{ $order->billingAddress->country->name }}<br>
                                    @if($order->customer?->vat_no)
                                        <p>VAT No.: {{ $order->customer?->vat_no }}</p>
                                    @endif
                                </td>

                                <td align="left" width="33%">
                                    <h3>Shipping</h3>
                                    {{ $order->shippingAddress->fullName }}<br>
                                    @if($order->shippingAddress->company_name)
                                      {{ $order->shippingAddress->company_name }}<br>
                                    @endif
                                    {{ $order->shippingAddress->line_one }}
                                    @if($order->shippingAddress->line_two)
                                      <br>{{ $order->shippingAddress->line_two }}<br>
                                    @endif
                                    @if($order->shippingAddress->line_three)
                                      <br>{{ $order->shippingAddress->line_three }}<br>
                                    @endif
                                    {{ $order->shippingAddress->city }}<br>
                                    {{ $order->shippingAddress->state }}<br>
                                    {{ $order->shippingAddress->postcode }}<br>
                                    {{ $order->shippingAddress->country->name }}<br>
                                </td>

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
                  @foreach($order->physicalLines as $line)
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
                        {{ $line->unit_price->formatted }}
                      </td>
                      <td>
                        {{ $line->discount_total->formatted }}
                      </td>
                      <td>
                        {{ $line->tax_breakdown->sum('percentage') }}%
                      </td>
                      <td>
                        {{ $line->tax_total->formatted }}
                      </td>

                      <td>
                        {{ $line->sub_total->formatted }}
                      </td>
                    </tr>
                  @endforeach
                </tbody>
                <tfoot class="lines-footer">
                    <tr>
                        <td colspan="5"></td>
                        <td colspan="2"><strong>Sub Total</strong></td>
                        <td>{{ $order->sub_total->formatted }}</td>
                    </tr>
                    @foreach($order->shippingLines as $line)
                      <tr>
                        <td colspan="4"></td>
                        <td colspan="3">
                          <strong>Shipping</strong><br>
                          <small>{{ strip_tags($line->description) }}</small>
                        </td>
                        <td>{{ $line->sub_total->formatted }}</td>
                      </tr>
                    @endforeach
                    <tr>
                        <td colspan="5"></td>
                        <td colspan="2"><strong>Tax</strong></td>
                        <td>{{ $order->tax_total->formatted }}</td>
                    </tr>
                    <tr>
                        <td colspan="5"></td>
                        <td colspan="2"><strong>Total</strong></td>
                        <td>{{ $order->total->formatted }}</td>
                    </tr>
                </tfoot>
            </table>

            @if($order->notes)
            <p><strong>Order Notes</strong><br>
            {{ $order->notes }}</p>
            <br>
            @endif
        </div>
    </div>
</body>
</html>
