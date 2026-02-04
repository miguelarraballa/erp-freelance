<div class="fi-wi-stats-overview">
    <div class="inline-flex flex-nowrap gap-3 overflow-x-auto py-1">
        @foreach ($stats as $stat)
            <div class="fi-wi-stats-overview-stat">
                <div class="fi-wi-stats-overview-stat-content">
                    <div class="fi-wi-stats-overview-stat-label-ctn">
                        <span class="fi-wi-stats-overview-stat-label whitespace-nowrap">
                            {{ $stat['label'] }}
                        </span>
                    </div>
                    <div class="fi-wi-stats-overview-stat-value">
                        {{ $stat['value'] }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
