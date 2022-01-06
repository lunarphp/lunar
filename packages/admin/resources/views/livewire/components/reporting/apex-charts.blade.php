<div
    style="width: 100%; height: 100%;"
    x-data="{
    init() {
        var el = document.querySelector('#' + this.$wire.get('key'));
        var options = this.$wire.get('options');

        var chart = new ApexCharts(el, options);
        chart.render();
    }
}"
    x-init="init()"
>
    <div id="{{ $this->key }}"></div>
</div>
