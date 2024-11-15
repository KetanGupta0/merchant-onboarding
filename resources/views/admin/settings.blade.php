<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Settings</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item active">Settings</li>
                </ol>
            </div>

        </div>
    </div>
</div>
<!-- end page title -->
@isset($admin)
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <h3>Personal Info</h3>
                <form action="{{url('/admin/settings/update-admin')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="text" name="admin_name" id="admin_name" class="form-control" placeholder="Name" value="{{$admin->admin_name}}">
                                <label for="admin_name">Name <span class="text-danger">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="email" name="admin_email" id="admin_email" class="form-control" placeholder="Email" value="{{$admin->admin_email}}">
                                <label for="admin_email">Email <span class="text-danger">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="text" name="admin_phone" id="admin_phone" class="form-control" placeholder="Primary Phone" value="{{$admin->admin_phone}}">
                                <label for="admin_phone">Primary Phone <span class="text-danger">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="text" name="admin_phone2" id="admin_phone2" class="form-control" placeholder="Secondary Phone" value="{{$admin->admin_phone2}}">
                                <label for="admin_phone2">Secondary Phone</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="file" name="admin_profile_pic" id="admin_profile_pic" accept="image/*" class="form-control" placeholder="Profile Photo">
                                <label for="admin_profile_pic">Profile Photo</label>
                            </div>
                        </div>
                    </div>
                    <h3>Address</h3>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="text" name="admin_city" id="admin_city" class="form-control" placeholder="City" value="{{$admin->admin_city}}">
                                <label for="admin_city">City</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="text" name="admin_state" id="admin_state" class="form-control" placeholder="State" value="{{$admin->admin_state}}">
                                <label for="admin_state">State</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="text" name="admin_country" id="admin_country" class="form-control" placeholder="Country" value="{{$admin->admin_country}}">
                                <label for="admin_country">Country</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="text" name="admin_zip_code" id="admin_zip_code" class="form-control" placeholder="Zip Code" value="{{$admin->admin_zip_code}}">
                                <label for="admin_zip_code">Zip Code</label>
                            </div>
                        </div>
                        <div class="col-md-8 mb-3">
                            <div class="form-floating">
                                <input type="text" name="admin_landmark" id="admin_landmark" class="form-control" placeholder="Landmark" value="{{$admin->admin_landmark}}">
                                <label for="admin_landmark">Landmark</label>
                            </div>
                        </div>
                    </div>
                    <h3>Password</h3>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="password" name="admin_password" id="admin_password" class="form-control" placeholder="Current Password">
                                <label for="admin_password">Current Password <span class="text-danger">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="password" name="admin_password_new" id="admin_password_new" class="form-control" placeholder="New Password">
                                <label for="admin_password_new">New Password</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="password" name="admin_password_new_confirmed" id="admin_password_new_confirmed" class="form-control" placeholder="Confirm New Password">
                                <label for="admin_password_new_confirmed">Confirm New Password</label>
                            </div>
                        </div>
                        <div class="col-md-12 text-end">
                            <input type="submit" value="Update" class="btn btn-primary">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endisset