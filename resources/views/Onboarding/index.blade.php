<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-11 col-sm-10 col-md-10 col-lg-6 col-xl-5 text-center p-0 mt-3 mb-2">
            <div class="card px-0 pt-4 pb-0 mt-3 mb-3">
                <h2 id="heading">Sign Up Your User Account</h2>
                <p>Fill all form field to go to next step</p>
                <form id="msform">
                    <input type="hidden" name="merchant_id" id="merchant_id" value="0">
                    <input type="hidden" name="business_id" id="business_id" value="0">
                    <!-- progressbar -->
                    <ul id="progressbar">
                        <li class="active" id="account"><strong>Account</strong></li>
                        <li id="personal"><strong>Personal</strong></li>
                        <li id="payment"><strong>Image</strong></li>
                        <li id="confirm"><strong>Finish</strong></li>
                    </ul>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
                    </div> <br> <!-- fieldsets -->
                    <fieldset>
                        <div class="form-card">
                            <div class="row">
                                <div class="col-7">
                                    <h2 class="fs-title">Owner/Merchant/Director Details/KYC:</h2>
                                </div>
                                <div class="col-5">
                                    <h2 class="steps">Step 1 - 4</h2>
                                </div>
                            </div>
                            <label class="fieldlabels" for="name">Name: *</label>
                            <input type="text" name="name" id="name" placeholder="Full Name" />

                            <label class="fieldlabels" for="mobile">Mobile: *</label>
                            <input type="tel" name="mobile" id="mobile" placeholder="Mobile Number" />

                            <label class="fieldlabels" for="email">Email: *</label>
                            <input type="email" name="email" id="email" placeholder="Email Id" />

                            <label class="fieldlabels" for="aadhar">Aadhar: *</label>
                            <input type="number" name="aadhar" id="aadhar" placeholder="Aadhar Number" />

                            <label class="fieldlabels" for="pan1">PAN: *</label>
                            <input type="text" name="pan1" id="pan1" placeholder="PAN Number" />

                            <div class="passBlock">
                                <label class="fieldlabels" for="pwd">Password: *</label>
                                <input type="password" name="pwd" id="pwd" placeholder="Password" />

                                <label class="fieldlabels" for="cpwd">Confirm Password: *</label>
                                <input type="password" name="cpwd" id="cpwd" placeholder="Confirm Password" />
                            </div>
                        </div>
                        <input type="button" name="next" class="action-button sf-2" value="Next" />
                        <input type="button" name="next" class="next action-button sf-2-btn" style="display: none;" value="Next" />
                    </fieldset>
                    <fieldset>
                        <div class="form-card">
                            <div class="row">
                                <div class="col-7">
                                    <h2 class="fs-title">Business Details:</h2>
                                </div>
                                <div class="col-5">
                                    <h2 class="steps">Step 2 - 4</h2>
                                </div>
                            </div>
                            <label class="fieldlabels" for="businessName">Business Name: *</label>
                            <input type="text" name="businessName" id="businessName" placeholder="Business Name" />
                            <label class="fieldlabels" for="businessType">Business Type: *</label>
                            <select name="businessType" id="businessType">
                                <option value="">Select</option>
                                <option value="Individual">Individual</option>
                                <option value="Limited">Limited</option>
                                <option value="OPC">OPC</option>
                                <option value="Private Limited">Private Limited</option>
                                <option value="Solo Proprietorship">Solo Proprietorship</option>
                            </select>
                            <label class="fieldlabels" for="businessAddress">Business Address: *</label>
                            <input type="text" name="businessAddress" id="businessAddress" placeholder="Business Address" />
                            <label class="fieldlabels" for="companyWebsite">Company Website: *</label>
                            <input type="text" name="companyWebsite" id="companyWebsite" placeholder="Alternate Contact No." />
                        </div>
                        <input type="button" name="next" class="action-button sf-3" value="Next" />
                        <input type="button" name="previous" class="action-button-previous sb-1" value="Previous" />
                        <input type="button" name="next" class="next action-button sf-3-btn" style="display: none;" value="Next" />
                        <input type="button" name="previous" class="previous action-button-previous sb-1-btn" style="display: none;" value="Previous" />
                    </fieldset>
                    <fieldset>
                        <div class="form-card">
                            <div class="row">
                                <div class="col-7">
                                    <h2 class="fs-title">Business KYC:</h2>
                                </div>
                                <div class="col-5">
                                    <h2 class="steps">Step 3 - 4</h2>
                                </div>
                            </div>
                            <div id="cpancin">
                                <label class="fieldlabels" for="pan">Company PAN: *</label>
                                <input type="file" name="pan" id="pan" accept="image/*">
                                <label class="fieldlabels" for="cin">CIN: *</label>
                                <input type="file" name="cin" id="cin" accept="image/*">
                            </div>
                            <label class="fieldlabels" for="gst">GST:</label>
                            <input type="file" name="gst" id="gst" accept="image/*">
                            <label class="fieldlabels" for="msme">MSME:</label>
                            <input type="file" name="msme" id="msme" accept="image/*">
                        </div>
                        <input type="button" name="next" class="action-button sf-4" value="Submit" />
                        <input type="button" name="previous" class="action-button-previous sb-2" value="Previous" />
                        <input type="button" name="next" class="next action-button sf-4-btn" style="display: none;" value="Submit" />
                        <input type="button" name="previous" class="previous action-button-previous sb-2-btn" style="display: none;" value="Previous" />
                    </fieldset>
                    <fieldset>
                        <div class="form-card">
                            <div class="row">
                                <div class="col-7">
                                    <h2 class="fs-title">Finish:</h2>
                                </div>
                                <div class="col-5">
                                    <h2 class="steps">Step 4 - 4</h2>
                                </div>
                            </div> <br><br>
                            <h2 class="purple-text text-center"><strong>SUCCESS !</strong></h2> <br>
                            <div class="row justify-content-center">
                                <div class="col-3">
                                    <img src="https://i.imgur.com/GwStPmg.png" class="fit-image">
                                </div>
                            </div> <br><br>
                            <div class="row justify-content-center">
                                <div class="col-7 text-center">
                                    <h5 class="purple-text text-center">You Have Successfully Signed Up</h5>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).on('click', '.sf-2', function() {
            const name = $('#name').val();
            const mobile = $('#mobile').val();
            const email = $('#email').val();
            const aadhar = $('#aadhar').val();
            const pan = $('#pan1').val();
            const pwd = $('#pwd').val();
            const cpwd = $('#cpwd').val();
            // Validation flags
            let isValid = true;
            let errorMessage = "";
            // Name validation: Check if empty or non-alphabetic characters
            if (!name) {
                errorMessage += "Name is required.<br>";
                isValid = false;
            } else if (!/^[a-zA-Z\s]+$/.test(name)) {
                errorMessage += "Name should only contain letters and spaces.<br>";
                isValid = false;
            }
            // Mobile validation: Check if empty or invalid format (10-digit number)
            if (!mobile) {
                errorMessage += "Mobile number is required.<br>";
                isValid = false;
            } else if (!/^\d{10}$/.test(mobile)) {
                errorMessage += "Mobile number must be a 10-digit number.<br>";
                isValid = false;
            }
            // Email validation: Check if empty or invalid email format
            if (!email) {
                errorMessage += "Email is required.<br>";
                isValid = false;
            } else if (!/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/.test(email)) {
                errorMessage += "Enter a valid email address.<br>";
                isValid = false;
            }
            // Aadhar validation: Check if empty or invalid (12-digit number)
            if (!aadhar) {
                errorMessage += "Aadhar number is required.<br>";
                isValid = false;
            } else if (!/^\d{12}$/.test(aadhar)) {
                errorMessage += "Aadhar number must be a 12-digit number.<br>";
                isValid = false;
            }
            // PAN validation: Check if empty or invalid format (e.g., ABCDE1234F)
            if (!pan) {
                errorMessage += "PAN number is required.<br>";
                isValid = false;
            } else if (!/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/.test(pan)) {
                errorMessage += "Enter a valid PAN number (e.g., ABCDE1234F).<br>";
                isValid = false;
            }
            // Password validation: Check if empty or less than 6 characters
            if (!pwd) {
                errorMessage += "Password is required.<br>";
                isValid = false;
            } else if (pwd.length < 6) {
                errorMessage += "Password should be at least 6 characters long.<br>";
                isValid = false;
            }
            // Confirm password validation: Check if matches the password
            if (!cpwd) {
                errorMessage += "Confirm Password is required.<br>";
                isValid = false;
            } else if (pwd !== cpwd) {
                errorMessage += "Passwords do not match.<br>";
                isValid = false;
            }
            // If validation passes, click the next button; otherwise, show errors
            if (isValid) {
                $.post("{{ url('merchant/onboarding/step-1') }}", {
                    merchant_name: name,
                    merchant_phone: mobile,
                    merchant_email: email,
                    merchant_aadhar_no: aadhar,
                    merchant_pan_no: pan,
                    merchant_password: pwd,
                    merchant_confirm_password: cpwd,
                }, function(response) {
                    if (response.status === true) {
                        $('#merchant_id').val(response.merchant_id);
                        $('.sf-2-btn').click();
                    }
                }).fail(function(error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: error.responseJSON.message
                    });
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errorMessage
                });
            }
        });
        $(document).on('click', '.sf-3', function() {
            const businessName = $('#businessName').val();
            const businessType = $('#businessType').val();
            const businessAddress = $('#businessAddress').val();
            const companyWebsite = $('#companyWebsite').val();
            let isValid = true;
            let errorMessage = "";
            if (!businessName) {
                errorMessage += "Business Name is required.<br>";
                isValid = false;
            }
            if (!businessType) {
                errorMessage += "Business Type is required.<br>";
                isValid = false;
            }
            if (!businessAddress) {
                errorMessage += "Business Address is required.<br>";
                isValid = false;
            }
            if (!companyWebsite) {
                errorMessage += "Company Website is required.<br>";
                isValid = false;
            }
            if (isValid) {
                $.post("{{ url('merchant/onboarding/step-2') }}", {
                    merchant_id: $('#merchant_id').val(),
                    business_id: $('#business_id').val(),
                    business_name: businessName,
                    business_type: businessType,
                    business_address: businessAddress,
                    business_website: companyWebsite
                }, function(response) {
                    if (response.status == true) {
                        $('#merchant_id').val(response.merchant_id);
                        $('#business_id').val(response.business_id);
                        $('.sf-3-btn').click();
                    }
                }).fail(function(error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: error.responseJSON.message
                    });
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errorMessage
                });
            }
        });
        $(document).on('click', '.sf-4', function() {
            let fileData = new FormData();
            let fields = [];
            let missingFields = [];

            if ($('#businessType').val() === 'Individual' || $('#businessType').val() === 'Solo Proprietorship') {
                fields = ['gst', 'msme'];
            } else {
                fields = ['pan', 'cin', 'gst', 'msme'];
                let fileInput1 = $('#pan')[0];
                let fileInput2 = $('#cin')[0];
                if (fileInput1 && fileInput1.files) {
                    if (fileInput1.files.length === 0) {
                        missingFields.push('pan');
                    }
                }
                if (fileInput2 && fileInput2.files) {
                    if (fileInput2.files.length === 0) {
                        missingFields.push('cin');
                    }
                }
            }

            // Display alert if required files are missing
            if (missingFields.length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please upload required files: ' + missingFields.join(', ')
                });
                return; // Stop execution if required files are missing
            }

            // Collect files to be uploaded
            fields.forEach(function(field) {
                let fileInput = $('#' + field)[0];
                if (fileInput.files.length > 0) {
                    fileData.append(field, fileInput.files[0]);
                }
            });

            // Add merchant and business ID to FormData
            fileData.append('merchant_id', $('#merchant_id').val() || 0);
            fileData.append('business_id', $('#business_id').val() || 0);
            fileData.append('business_type', $('#businessType').val());

            // Send files to the server via AJAX
            $.ajax({
                url: "{{ url('merchant/onboarding/step-3') }}",
                type: 'POST',
                data: fileData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response === true) {
                        $('.sf-4-btn').click();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Something went wrong!'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error uploading files:\n", error);
                }
            });
        });

        $(document).on('click', '.sb-1', function() {
            $('.sb-1-btn').click();
        });
        $(document).on('click', '.sb-2', function() {
            $('.sb-2-btn').click();
        });
        $(document).on('blur', '#mobile', function() {
            $.post("{{ url('merchant/onboarding/check-phone') }}", {
                merchant_phone: $(this).val()
            }, function(response) {
                if (response.status == true) {
                    fillData(response.data, response.businessData);
                }
            }).fail(function(error) {
                console.log(error);
            });
        });
        $(document).on('blur', '#email', function() {
            $.post("{{ url('merchant/onboarding/check-email') }}", {
                merchant_email: $(this).val()
            }, function(response) {
                if (response.status == true) {
                    fillData(response.data, response.businessData);
                }
            }).fail(function(error) {
                console.log(error);
            });
        });
        $(document).on('blur', '#aadhar', function() {
            $.post("{{ url('merchant/onboarding/check-aadhar') }}", {
                merchant_aadhar_no: $(this).val()
            }, function(response) {
                if (response.status == true) {
                    fillData(response.data, response.businessData);
                }
            }).fail(function(error) {
                console.log(error);
            });
        });
        $(document).on('blur', '#pan1', function() {
            $.post("{{ url('merchant/onboarding/check-pan') }}", {
                merchant_pan_no: $(this).val()
            }, function(response) {
                if (response.status == true) {
                    fillData(response.data, response.businessData);
                }
            }).fail(function(error) {
                console.log(error);
            });
        });

        function fillData(data, businessData) {
            $('#merchant_id').val(data.merchant_id);
            $('#name').val(data.merchant_name);
            $('#mobile').val(data.merchant_phone);
            $('#email').val(data.merchant_email);
            $('#aadhar').val(data.merchant_aadhar_no);
            $('#pan1').val(data.merchant_pan_no);
            $('.passBlock').hide();
            $('#pwd').val('123456');
            $('#cpwd').val('123456');
            $('#business_id').val(businessData.business_id);
            $('#businessName').val(businessData.business_name);
            $('#businessType').val(businessData.business_type);
            $('#businessAddress').val(businessData.business_address);
            $('#companyWebsite').val(businessData.business_website);
            if (businessData.business_type == 'Individual' || businessData.business_type == 'Solo Proprietorship') {
                $('#cpancin').hide();
            } else {
                $('#cpancin').show();
            }
        }
        $(document).on('change', '#businessType', function() {
            const value = $(this).val();
            if (value == 'Individual' || value == 'Solo Proprietorship') {
                $('#cpancin').hide();
            } else {
                $('#cpancin').show();
            }
        });
    });
</script>
