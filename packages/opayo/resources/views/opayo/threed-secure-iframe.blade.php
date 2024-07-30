<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <form id="pa-form" method="POST" action="{{ app()->request->acsUrl }}">
        @if(app()->request->paReq)
            <input type="hidden" name="PaReq" value="{{ str_replace(' ', '+', app()->request->paReq) }}">
        @else
            <input type="hidden" name="creq" value="{{ app()->request->cReq }}" />
        @endif
        <input type="hidden" name="TermUrl" value="{{ route('opayo.threed.response') }}">
        <input type="hidden" name="MD" value="{{ Str::random(30) }}">
    </form>
    <script>document.addEventListener("DOMContentLoaded",function(){var b=document.getElementById("pa-form");b&&b.submit()})</script>
</body>
</html>