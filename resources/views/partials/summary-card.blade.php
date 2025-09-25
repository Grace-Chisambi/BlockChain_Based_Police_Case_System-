<div class="col-md-4">
    <div class="card bg-white text-primary border-0 shadow-sm rounded-lg hover:shadow-md transition-shadow">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-subtitle mb-2 text-primary">{{ $card['title'] }}</h6>
                    <div class="card-title fs-3">{{ $card['count'] }}</div>
                    
                </div>
                <i class="fas fa-{{ $card['icon'] }} fa-3x text-primary"></i>
            </div>
        </div>
    </div>
</div>
