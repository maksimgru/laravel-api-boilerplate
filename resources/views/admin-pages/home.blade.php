@extends('layouts.auth')

@section('content')

<h1 class="mt-4">Dashboard</h1>

<div class="hidden row">
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary text-white mb-4">
            <div class="card-body">Primary Card</div>
            <div class="card-footer">
                <a id="ctrl-primary-card"
                   href="#primary-card-more"
                   class="small text-white stretched-link d-flex justify-content-between collapsed"
                   data-toggle="collapse"
                   aria-expanded="true"
                   aria-controls="primary-card"
                >
                    <span>View Details</span>
                    <div><i class="fa fa-angle-down small text-white collapse-ctrl-arrow"></i></div>
                </a>
                <div id="primary-card-more" class="collapse" aria-labelledby="ctrl-waprimaryrning-card">
                    Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon
                    officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3
                    wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et.
                    Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan
                    excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt
                    you probably haven't heard of them accusamus labore sustainable VHS.
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-warning text-white mb-4">
            <div class="card-body">Warning Card</div>
            <div class="card-footer">
                <a id="ctrl-warning-card"
                   href="#warning-card-more"
                   class="small text-white stretched-link d-flex justify-content-between collapsed"
                   data-toggle="collapse"
                   aria-expanded="true"
                   aria-controls="warning-card"
                >
                    <span>View Details</span>
                    <div><i class="fa fa-angle-down small text-white collapse-ctrl-arrow"></i></div>
                </a>
                <div id="warning-card-more" class="collapse" aria-labelledby="ctrl-warning-card">
                    Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon
                    officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3
                    wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et.
                    Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan
                    excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt
                    you probably haven't heard of them accusamus labore sustainable VHS.
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-success text-white mb-4">
            <div class="card-body">
                Success Card
            </div>
            <div class="card-footer">
                <a id="ctrl-success-card"
                   href="#success-card-more"
                   class="small text-white stretched-link d-flex justify-content-between collapsed"
                   data-toggle="collapse"
                   aria-expanded="true"
                   aria-controls="success-card"
                >
                    <span>View Details</span>
                    <div><i class="fa fa-angle-down small text-white collapse-ctrl-arrow"></i></div>
                </a>
                <div id="success-card-more" class="collapse" aria-labelledby="ctrl-success-card">
                        Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon
                        officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3
                        wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et.
                        Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan
                        excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt
                        you probably haven't heard of them accusamus labore sustainable VHS.
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-danger text-white mb-4">
            <div class="card-body">Danger Card</div>
            <div class="card-footer">
                <a id="ctrl-danger-card"
                   href="#danger-card-more"
                   class="small text-white stretched-link d-flex justify-content-between collapsed"
                   data-toggle="collapse"
                   aria-expanded="true"
                   aria-controls="danger-card"
                >
                    <span>View Details</span>
                    <div><i class="fa fa-angle-down small text-white collapse-ctrl-arrow"></i></div>
                </a>
                <div id="danger-card-more" class="collapse" aria-labelledby="ctrl-danger-card">
                    Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon
                    officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3
                    wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et.
                    Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan
                    excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt
                    you probably haven't heard of them accusamus labore sustainable VHS.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="hidden row">
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header"><i class="fa fa-chart-area mr-1"></i>Area Chart Example</div>
            <div class="card-body"><canvas class="myAreaChart" width="100%" height="40"></canvas></div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header"><i class="fa fa-chart-bar mr-1"></i>Bar Chart Example</div>
            <div class="card-body"><canvas class="myBarChart" width="100%" height="40"></canvas></div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header"><i class="fa fa-chart-bar mr-1"></i>Pie Chart Example</div>
            <div class="card-body"><canvas class="myPieChart" width="100%" height="40"></canvas></div>
        </div>
    </div>
</div>

@endsection
