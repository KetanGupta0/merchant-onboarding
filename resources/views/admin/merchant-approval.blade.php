<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Merchant Approval</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item active">Merchant Approval</li>
                </ol>
            </div>

        </div>
    </div>
</div>
<!-- end page title -->
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive" style="min-height: 50vh;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>S. No.</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Joining Date</th>
                            <th>Is Onboarded</th>
                            <th>Verification Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($merchants))
                            @foreach ($merchants as $merchant)
                                <tr>
                                    <td>{{$loop->index+1}}</td>
                                    <td>{{$merchant->merchant_name}}</td>
                                    <td>{{$merchant->merchant_phone}}</td>
                                    <td>{{$merchant->merchant_email}}</td>
                                    <td>{{date('d M Y',strtotime($merchant->created_at))}}</td>
                                    <td>{{$merchant->merchant_is_onboarded}}</td>
                                    <td>{{$merchant->merchant_is_verified}}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-icon btn-sm fs-16 text-muted dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-more-fill"></i>
                                            </button>
                                            <ul class="dropdown-menu" style="">
                                                <li><a class="dropdown-item" href="javascript:void(0);"><i class="ri-eye-fill text-muted me-2 align-bottom"></i>View</a></li>
                                                <li><a class="dropdown-item" href="javascript:void(0);"><i class="ri-star-fill text-muted me-2 align-bottom"></i>Approve</a></li>
                                                <li><a class="dropdown-item" href="javascript:void(0);"><i class="ri-delete-bin-5-fill text-muted me-2 align-bottom"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>