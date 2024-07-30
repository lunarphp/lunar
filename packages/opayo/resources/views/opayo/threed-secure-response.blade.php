<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <div style="text-align: center;display: flex;align-content: center;justify-content: center;">
        Processing...
    </div>
    <script>
        (function () {
            if ( typeof window.CustomEvent === "function" ) return false;
            function CustomEvent ( event, params ) {
                params = params || { bubbles: false, cancelable: false, detail: undefined };
                var evt = document.createEvent('CustomEvent');
                evt.initCustomEvent( event, params.bubbles, params.cancelable, params.detail );
                return evt;
            }
            CustomEvent.prototype = window.Event.prototype;
            window.CustomEvent = CustomEvent;
        })();

        var myEvent = new CustomEvent('opayo_threed_secure_response', {
            detail: {
                @if($PaRes ?? false)
                PaRes: "{{ $PaRes }}",
                @endif
                @if($cres ?? false)
                cres: "{{ $cres }}",
                @endif
                @if($md ?? false)
                MD: "{{ $MD }}",
                @endif
                MDX: @if (!empty($mdx)) "{{ $MDX }}" @else null @endif
            }
        })
        window.parent.dispatchEvent(myEvent)
    </script>
</body>
</html>