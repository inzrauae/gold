(function () {
    'use strict';

    function initCalculator() {
        var calc = document.getElementById('calculator');
        if (!calc) return;

        var weightInput = document.getElementById('calc-weight');
        var unitSelect = document.getElementById('calc-unit');
        var karatSelect = document.getElementById('calc-karat');
        var output = document.getElementById('calc-output');
        if (!weightInput || !unitSelect || !karatSelect || !output) return;

        var gramsPerUnit = { gram: 1, '8g': 8, sovereign: 8, ounce: 31.1034768 };

        function format(n) {
            return n.toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function recalc() {
            var karat = karatSelect.value;
            var pricePerGram = parseFloat(calc.getAttribute('data-price-' + karat + 'k')) || 0;
            var weight = parseFloat(weightInput.value) || 0;
            var grams = weight * (gramsPerUnit[unitSelect.value] || 1);
            output.textContent = format(grams * pricePerGram);
            output.classList.remove('pulse');
            void output.offsetWidth;
            output.classList.add('pulse');
        }

        weightInput.addEventListener('input', recalc);
        unitSelect.addEventListener('change', recalc);
        karatSelect.addEventListener('change', recalc);
        recalc();
    }

    function initChart() {
        var canvas = document.getElementById('price-chart');
        if (!canvas || !canvas.getContext) return;

        var dataScript = document.getElementById('chart-data');
        var series = [];
        try {
            series = JSON.parse((dataScript && dataScript.textContent) || '[]');
        } catch (e) {
            series = [];
        }

        var lines = [
            { key: '24k', color: '#f3d67a', label: '24K', width: 1.5 },
            { key: '22k', color: '#d4af37', label: '22K', width: 2.5 },
            { key: '21k', color: '#b3922f', label: '21K', width: 1.5 },
            { key: '18k', color: '#8a6d1a', label: '18K', width: 1.5 }
        ];

        var ctx = canvas.getContext('2d');

        function draw() {
            var parent = canvas.parentElement;
            var cssWidth = Math.max(280, parent ? parent.clientWidth : canvas.width);
            var aspect = cssWidth < 480 ? 0.62 : (320 / 900);
            var cssHeight = Math.max(200, Math.min(420, Math.round(cssWidth * aspect)));
            var dpr = window.devicePixelRatio || 1;

            canvas.style.width = cssWidth + 'px';
            canvas.style.height = cssHeight + 'px';
            canvas.width = Math.round(cssWidth * dpr);
            canvas.height = Math.round(cssHeight * dpr);
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            ctx.clearRect(0, 0, cssWidth, cssHeight);

            if (!series.length) {
                ctx.fillStyle = '#857a6c';
                ctx.font = '14px system-ui, -apple-system, sans-serif';
                ctx.fillText('No price history yet.', 16, cssHeight / 2);
                return;
            }

            var padding = { top: 34, right: 16, bottom: 28, left: 60 };
            var plotW = cssWidth - padding.left - padding.right;
            var plotH = cssHeight - padding.top - padding.bottom;

            var values = [];
            series.forEach(function (row) {
                lines.forEach(function (line) {
                    if (typeof row[line.key] === 'number') values.push(row[line.key]);
                });
            });
            if (!values.length) return;

            var min = Math.min.apply(null, values);
            var max = Math.max.apply(null, values);
            if (min === max) { min -= 1; max += 1; }
            var pad = (max - min) * 0.08;
            min -= pad;
            max += pad;

            function x(i) {
                return padding.left + (series.length === 1 ? plotW / 2 : (i / (series.length - 1)) * plotW);
            }
            function y(v) {
                return padding.top + plotH - ((v - min) / (max - min)) * plotH;
            }

            ctx.strokeStyle = 'rgba(212, 175, 55, 0.12)';
            ctx.lineWidth = 1;
            ctx.fillStyle = '#857a6c';
            ctx.font = '11px system-ui, -apple-system, sans-serif';
            ctx.textAlign = 'right';
            ctx.textBaseline = 'middle';

            var gridLines = 4;
            for (var g = 0; g <= gridLines; g++) {
                var gv = min + ((max - min) * g) / gridLines;
                var gy = y(gv);
                ctx.beginPath();
                ctx.moveTo(padding.left, gy);
                ctx.lineTo(cssWidth - padding.right, gy);
                ctx.stroke();
                ctx.fillText(Math.round(gv).toLocaleString('en-LK'), padding.left - 8, gy);
            }

            ctx.textAlign = 'center';
            ctx.textBaseline = 'top';
            [0, Math.floor((series.length - 1) / 2), series.length - 1].forEach(function (i) {
                if (i < 0 || i >= series.length || !series[i].date) return;
                ctx.fillText(String(series[i].date).slice(5), x(i), cssHeight - padding.bottom + 8);
            });

            lines.forEach(function (line) {
                ctx.beginPath();
                var started = false;
                series.forEach(function (row, i) {
                    if (typeof row[line.key] !== 'number') return;
                    var px = x(i), py = y(row[line.key]);
                    if (!started) { ctx.moveTo(px, py); started = true; } else { ctx.lineTo(px, py); }
                });
                ctx.strokeStyle = line.color;
                ctx.lineWidth = line.width;
                ctx.lineJoin = 'round';
                ctx.stroke();
            });

            ctx.textAlign = 'left';
            ctx.textBaseline = 'alphabetic';
            var lx = padding.left;
            var ly = 16;
            lines.forEach(function (line) {
                ctx.fillStyle = line.color;
                ctx.fillRect(lx, ly - 8, 10, 10);
                ctx.fillStyle = '#f5efe0';
                ctx.font = '11px system-ui, -apple-system, sans-serif';
                ctx.fillText(line.label, lx + 14, ly);
                lx += ctx.measureText(line.label).width + 30;
            });
        }

        draw();
        var resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(draw, 150);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initCalculator();
            initChart();
        });
    } else {
        initCalculator();
        initChart();
    }
})();
