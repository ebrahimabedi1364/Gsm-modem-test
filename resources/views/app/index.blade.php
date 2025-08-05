@extends('app.layouts.master')

@section('content')
    <div class="container py-4">

        <ul class="nav nav-tabs justify-content-center mb-4" id="cityTabs" role="tablist">
            @foreach ($data as $index => $city)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="tab-{{ $index }}"
                        data-bs-toggle="tab" data-bs-target="#city-{{ $index }}" type="button" role="tab"
                        aria-controls="city-{{ $index }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                        {{ $city['cityName'] }}
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="tab-content" id="cityTabsContent">
            @foreach ($data as $index => $city)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="city-{{ $index }}"
                    role="tabpanel" aria-labelledby="tab-{{ $index }}">
                    <div class="industrial_container">

                        <div class="row">
                            <!-- نمودار گراف -->
                            <div class="col-md-7 mb-4">
                                <div id="graph-{{ $index }}" class="graph-container"></div>
                            </div>

                            <!-- نمودار میله‌ای و وضعیت تجهیزات -->
                            <div class="col-md-5">
                                <div class="row">
                                    <div class="col-6">
                                        <canvas id="current-volume-chart-{{ $index }}"
                                            style="height: 200px;"></canvas>
                                    </div>
                                    <div class="col-6">
                                        <canvas id="available-storage-chart-{{ $index }}"
                                            style="height: 200px;"></canvas>
                                    </div>
                                </div>

                                <div class="equipment-status-container mt-4">
                                    <h5 class="title">وضعیت تجهیزات</h5>
                                    <ul class="equipment-list">
                                        @foreach ($city['nodes'] as $node)
                                            @php
                                                $status = strtolower($node['status']);
                                            @endphp
                                            <li class="equipment-item">
                                                <span class="status-indicator {{ $status == 'on' ? 'on' : 'off' }}"></span>
                                                <span class="equipment-label">{{ $node['label'] }}</span>
                                                <div class="status-controls">
                                                    @livewire('datalogger-toggle', ['datalogger' => $node['dataloggerId']], key($node['dataloggerId']))
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- کتابخانه‌های مورد نیاز -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const cities = @json($data);
        const chartInstances = {};
        const graphRendered = {};
    
        function renderChartsForIndex(index) {
            const city = cities[index];
            if (!city) return;
    
            // جلوگیری از اجرای دوباره
            if (!graphRendered[index]) {
                drawD3Graph(city, index);
                graphRendered[index] = true;
            }
    
            if (!chartInstances[`volume-${index}`]) {
                const ctxVolume = document.getElementById(`current-volume-chart-${index}`)?.getContext('2d');
                const ctxStorage = document.getElementById(`available-storage-chart-${index}`)?.getContext('2d');
    
                if (!ctxVolume || !ctxStorage) return;
    
                chartInstances[`volume-${index}`] = new Chart(ctxVolume, {
                    type: 'bar',
                    data: {
                        labels: ['حجم فعلی', 'ظرفیت'],
                        datasets: [{
                            label: 'حجم فعلی (متر مکعب)',
                            data: [city.currentVolum, city.capacity],
                            backgroundColor: ['rgba(54, 162, 235, 0.5)', 'rgba(75, 192, 192, 0.5)'],
                            borderColor: ['rgba(54, 162, 235, 1)', 'rgba(75, 192, 192, 1)'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
    
                chartInstances[`storage-${index}`] = new Chart(ctxStorage, {
                    type: 'bar',
                    data: {
                        labels: ['تولید', 'کل'],
                        datasets: [{
                            label: 'تولید (لیتر/ثانیه)',
                            data: [city.availableStorage, city.totalFlowRate],
                            backgroundColor: ['rgba(255, 99, 132, 0.5)', 'rgba(255, 159, 64, 0.5)'],
                            borderColor: ['rgba(255, 99, 132, 1)', 'rgba(255, 159, 64, 1)'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }
        }
    
        // -------------------- گراف D3 --------------------
        function drawD3Graph(city, index) {
            const nodes = city.nodes;
            const links = city.links;
    
            const width = Math.max(500, 100 * nodes.length);
            const height = Math.max(500, 100 * nodes.length);
    
            const svg = d3.select(`#graph-${index}`)
                .append("svg")
                .attr("viewBox", `0 0 ${width} ${height}`)
                .attr("preserveAspectRatio", "xMidYMid meet")
                .style("background-color", "#f9f9f9")
                .style("border-radius", "10px")
                .style("padding", "15px")
                .style("box-shadow", "0 4px 12px rgba(0, 0, 0, 0.1)");
    
            const simulation = d3.forceSimulation(nodes)
                .force("link", d3.forceLink(links).id(d => d.id).distance(150))
                .force("charge", d3.forceManyBody().strength(-300))
                .force("center", d3.forceCenter(width / 2, height / 2))
                .force("collide", d3.forceCollide(100));
    
            const link = svg.append("g")
                .selectAll("line")
                .data(links)
                .enter().append("line")
                .attr("stroke", "#888")
                .attr("stroke-width", 2);
    
            const node = svg.append("g")
                .selectAll("g")
                .data(nodes)
                .enter().append("g")
                .attr("class", "node");
    
            node.each(function (d) {
                if (d.group === 'source') {
                    const totalHeight = 150;
                    const levelValue = +d.level;
                    const percentage = levelValue / 4;
                    const blueHeight = totalHeight * percentage;
    
                    d3.select(this).append("rect")
                        .attr("x", -20).attr("y", 0)
                        .attr("width", 110).attr("height", totalHeight)
                        .attr("fill", "#d3d3d3").attr("rx", 10).attr("ry", 10);
    
                    d3.select(this).append("rect")
                        .attr("x", -20).attr("y", totalHeight - blueHeight)
                        .attr("width", 110).attr("height", blueHeight)
                        .attr("fill", "#344CB7");
    
                    d3.select(this).append("text")
                        .attr("x", 0).attr("dy", totalHeight + 20)
                        .attr("text-anchor", "middle")
                        .style("font-size", "14px").style("fill", "#333")
                        .text(d.label);
    
                    d3.select(this).append("text")
                        .attr("x", 0).attr("y", 70).attr("text-anchor", "middle")
                        .style("font-size", "12px").style("fill", "#fff")
                        .text(`level: ${levelValue}`);
    
                } else if (d.group === 'pump') {
                    d3.select(this).append("image")
                        .attr("xlink:href", "{{ asset('assets/images/pump.png') }}")
                        .attr("x", -30).attr("y", -30).attr("width", 60).attr("height", 60);
    
                    d3.select(this).append("text")
                        .attr("x", 0).attr("y", 40).attr("text-anchor", "middle")
                        .style("font-size", "14px").style("fill", "#333")
                        .text(d.label);
                } else if (d.group === 'well') {
                    d3.select(this).append("image")
                        .attr("xlink:href", "{{ asset('assets/images/well.png') }}")
                        .attr("x", -40).attr("y", -40).attr("width", 80).attr("height", 80);
    
                    d3.select(this).append("text")
                        .attr("x", 0).attr("y", 55).attr("text-anchor", "middle")
                        .style("font-size", "14px").style("fill", "#333")
                        .text(d.label);
                }
            });
    
            simulation.on("tick", () => {
                link.attr("x1", d => d.source.x)
                    .attr("y1", d => d.source.y)
                    .attr("x2", d => d.target.x)
                    .attr("y2", d => d.target.y);
    
                node.attr("transform", d => `translate(${d.x},${d.y})`);
            });
        }
    
        // اولین تب هنگام بارگذاری
        renderChartsForIndex(0);
    
        // وقتی تب جدید فعال می‌شه
        document.querySelectorAll('button[data-bs-toggle="tab"]').forEach((tabBtn) => {
            tabBtn.addEventListener('shown.bs.tab', function () {
                const tabId = tabBtn.getAttribute('id'); // مثل tab-1
                const index = tabId.split('-')[1]; // فقط عدد
                renderChartsForIndex(index);
            });
        });
    </script>
    


    <style>
        .graph-container {
            min-height: 500px;
            width: 100%;
            background-color: #f5f5f5;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .equipment-status-container {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .equipment-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .equipment-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            margin-bottom: 10px;
            background: linear-gradient(145deg, #ffffff, #e6e6e6);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .equipment-label {
            flex-grow: 1;
            font-weight: bold;
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 10px;
            animation: blink 2s infinite;
        }

        .status-indicator.on {
            background-color: #4caf50;
        }

        .status-indicator.off {
            background-color: #f44336;
        }

        @keyframes blink {
            50% {
                opacity: 0.5;
            }
        }

        .status-controls {
            padding: 5px 10px;
            border-radius: 5px;
            background: linear-gradient(145deg, #f1f1f1, #e0e0e0);
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .nav-tabs .nav-link {
            font-weight: bold;
            color: #555;
        }

        .nav-tabs .nav-link.active {
            color: #0d6efd;
            background-color: #e9ecef;
            border-color: #dee2e6 #dee2e6 #fff;
        }
    </style>
@endsection
