<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $fileName }}</title>

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->

    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }

        p {
            margin-bottom: -13px
        }

        span {
            font-weight: bold;
        }
    </style>
    <style>
        .demo {
            border: 1px solid #050505;
            border-collapse: collapse;
            padding: 5px;
        }

        .demo th {
            border: 1px solid #050505;
            padding: 5px;
            background: #A39F9F;
        }

        .demo td {
            border: 1px solid #050505;
            text-align: center;
            padding: 5px;
        }
    </style>

</head>

<body class="antialiased">
    <h2 style="margin: -2px;">{{ $prescription->patient->user->name }}</h2>
    <hr style="border: 1px solid">
    <table width="100%" border="0" style="margin:20px 0">
        <tr style="vertical-align: top;">
            <td width="100">Pasien</td>
            <td width="1">:</td>
            <td>{{ $prescription->patient->user->name }} <br> {{ $prescription->patient->user->address }}</td>
        </tr>
        <tr>
            <td width="100">Tanggal</td>=>
            <td>:</td>
            <td>{{ $prescription->prescription_date }}</td>
        </tr>
    </table>

    <table width="100%" class="demo">
        <thead>
            <tr>
                <th width="50%">Nama Obat</th>
                <th width="15%">Harga</th>
                <th width="10%">Qty</th>
                <th width="15%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($prescription->prescribedDrugs->sortBy('id') as $item)
                <tr>
                    <td>{{ $item->drugs->name }}</td>
                    <td>Rp. {{ number_format($item->drugs->price, 0, ',', '.') }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>Rp. {{ number_format($item->drugs->price * $item->quantity, 0, ',', '.') }}</td>
                    @php $total += $item->drugs->price * $item->quantity; @endphp
                </tr>
            @endforeach
            <tr>
                <td colspan="3">Total</td>
                <td>Rp. {{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <p>&nbsp;Terimakasih.</p>
    {{-- <table width="100%" border="0" style="margin:5px 0">
        <tr>
            <td>
                <h4 style="margin-bottom: 0">Informasi Staff:</h4>
            </td>
        </tr>
        <tr>
            <td>{{ $prescription->staff->user->name }}</td>
        </tr>
        <tr>
            <td>{{ $prescription->staff->user->email }}</td>
        </tr>
        <tr>
            <td>{{ $prescription->staff->user->contact_number }}</td>
        </tr>
    </table> --}}

    <table width="100%" border="0" style="margin:5px 0">
        <tr>
            <td>
                <h4 style="margin-bottom: 0">Informasi Dokter:</h4>
            </td>
        </tr>
        <tr>
            <td>{{ $prescription->doctor->user->name }}</td>
        </tr>
        <tr>
            <td>{{ $prescription->doctor->user->email }}</td>
        </tr>
        <tr>
            <td>{{ $prescription->doctor->user->contact_number }}</td>
        </tr>
    </table>

    <table width="100%" border="0" style="margin:5px 0">
        <tr>
            <td>
                <h4 style="margin-bottom: 0">Informasi Apoteker:</h4>
            </td>
        </tr>
        <tr>
            <td>{{ $prescription->staff->user->name }}</td>
        </tr>
        <tr>
            <td>{{ $prescription->staff->user->email }}</td>
        </tr>
        <tr>
            <td>{{ $prescription->staff->user->contact_number }}</td>
        </tr>
    </table>
    {{-- <table width="100%" border="0" style="margin:5px 0">
        <tr>
            <td colspan="3">
                <h4 style="margin-bottom:-3px">Pembayaran:</h4>
            </td>
            @if ($prescription->ach_transfer == true)
        </tr>
        <tr>
            <td colspan="3">USD account details<span>(for ACH transfer):</span></td>
        </tr>
        <tr>
            <td width="165">Account Holder</td>
            <td width="1"> : </td>
            <td><span>{{ $prescription->worker_name }}</span></td>
        </tr>
        <tr>
            <td>ACH and Wire routing number</td>
            <td> : </td>
            <td><span>{{ $prescription->ach_routing_number }}</span></td>
        </tr>
        <tr>
            <td>Account Number</td>
            <td> : </td>
            <td><span>{{ $prescription->ach_account_number }}</span></td>
        </tr>
        <tr style="vertical-align:top">
            <td>Address</td>
            <td> : </td>
            <td><span>{{ $prescription->ach_account_address }}</span></td>
        </tr>
        <tr>
            <td colspan="3">
                <h4 style="margin-bottom:-1px">Or, Paypal:</h4>
            </td>
        </tr>
        @endif
        <tr>
            <td colspan="3">
                <a href="{{ $prescription->payment_link }}" style="font-size: 17px">{{ $prescription->payment_link }}</a>
            </td>
        </tr>
        <tr>
            <td colspan="3">*Pembayaran harus sesuai dengan total.</td>
        </tr>
    </table>  --}}
</body>

</html>
