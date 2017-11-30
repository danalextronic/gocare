@extends('layouts.app')

@section('content')
    <div class="container spark-screen">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Create New Claim</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-5">
                                <form id="order-form" name="order-form">
                                    <div class="form-group">
                                        <label>Student Name</label>
                                        <input type="text" name="student_name" id="student_name" class="form-control">
                                    </div>

                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" id="email" class="form-control" required>
                                    </div>

                                    <div class="form-group">
                                        <label>Phone Number</label>
                                        <input type="text" name="phone_number" id="phone_number" class="form-control" required>
                                    </div>

                                    <div class="form-group">
                                        <label>Serial Number</label>
                                        <input type="text" name="serial_number" id="serial_number" class="form-control" required>
                                    </div>

                                    <h3>Claim Information</h3>
                                    <p>Was this device lost or stolen?</p>
                                    <div class="form-group">
                                        <div class="radio"><label><input type="radio" name="type" value="adew"> : No</label></div>
                                        <div class="radio"><label><input type="radio" name="type" value="theft"> : Yes</label></div>
                                    </div>


                                    <div id="adew">
                                        <div class="form-group">
                                            <label>When did the issues start?</label>
                                            <input type="date" class="form-control" name="question[6]">
                                        </div>

                                        <div class="form-group">
                                            <label>Has the device been exposed to water?</label>
                                            <div class="radio"><label><input type="radio" name="question[7]" value="No"> : No</label></div>
                                            <div class="radio"><label><input type="radio" name="question[7]" value="Yes"> : Yes</label></div>
                                        </div>

                                        <div class="form-group">
                                            <label>Has the device been restored?</label>
                                            <div class="radio"><label><input type="radio" name="question[8]" value="No"> : No</label></div>
                                            <div class="radio"><label><input type="radio" name="question[8]" value="Yes"> : Yes</label></div>
                                        </div>

                                        <div class="form-group">
                                            <label>Are the damages / issues with your device due to an accident?</label>
                                            <div class="radio"><label><input type="radio" name="question[9]" value="No"> : No</label></div>
                                            <div class="radio"><label><input type="radio" name="question[9]" value="Yes"> : Yes</label></div>
                                        </div>

                                        <div class="form-group">
                                            <label>Does the device still power on?</label>
                                            <div class="radio"><label><input type="radio" name="question[10]" value="No"> : No</label></div>
                                            <div class="radio"><label><input type="radio" name="question[10]" value="Yes"> : Yes</label></div>
                                        </div>

                                        <div class="form-group">
                                            <label>If applicable, would you like a loaner phone?</label>
                                            <div class="radio"><label><input type="radio" name="question[11]" value="No"> : No</label></div>
                                            <div class="radio"><label><input type="radio" name="question[11]" value="Yes"> : Yes</label></div>
                                        </div>

                                        <div class="form-group">
                                            <label>Describe the circumstances of the incident, in detail</label>
                                            <textarea name="question[12]" class="form-control"></textarea>
                                        </div>

                                        <div class="form-group">
                                            <label>

                                                What damage did the device sustain (broken glass, damaged corners, color changes, functionality loss, etc)</label>
                                            <textarea name="question[13]" class="form-control"></textarea>
                                        </div>

                                    </div>



                                    <div id="theft">
                                        <div class="form-group">
                                            <label>Incident Date/Time</label>
                                            <input type="date" name="question[14]" class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <label>Incident Location</label>
                                            <input type="text" name="question[15]" class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <label>Was this device lost or stolen?</label>
                                            <select name="question[16]" class="form-control">
                                                <option value="Lost">Lost</option>
                                                <option value="Stolen">Stolen</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Who was present when you noticed the device was lost or stolen?</label>
                                            <textarea name="question[17]" class="form-control"></textarea>
                                        </div>

                                        <div class="form-group">
                                            <p>

                                                If a Police Report was filed, please provide a copy
                                                A police report is required documentation for any theft claim. Please provide a copy to <a href="mailto:claims@gocare.com">claims@gocare.com</a>. Once this is received your claim will be reviewed for approval.
                                            </p>
                                        </div>

                                        <div class="form-group">
                                            <label>If a Police Report was not filed, please explain why</label>
                                            <textarea name="question[19]" class="form-control"></textarea>
                                        </div>

                                        <div class="form-group">
                                            <label>Were there any other items that were lost or stolen?</label>
                                            <div class="radio"><label><input type="radio" name="question[20]" value="No"> : No</label></div>
                                            <div class="radio"><label><input type="radio" name="question[20]" value="Yes"> : Yes</label></div>
                                        </div>

                                        <div class="form-group">
                                            <label>Was there forced entry?</label>
                                            <div class="radio"><label><input type="radio" name="question[21]" value="No"> : No</label></div>
                                            <div class="radio"><label><input type="radio" name="question[21]" value="Yes"> : Yes</label></div>
                                        </div>

                                        <div class="form-group">
                                            <label>Detailed description of the incident (including where the loss or theft occurred, how and when it was discovered and by whom?)</label>
                                            <textarea name="question[22]" class="form-control"></textarea>
                                        </div>

                                        <div class="form-group">
                                            <label>Additional information that will assist with our investigation</label>
                                            <textarea name="question[23]" class="form-control"></textarea>
                                        </div>

                                    </div>





                                    <div class="form-group">
                                        <div class="checkbox"><label><input type="checkbox"></label></div>
                                    </div>

                                    <h3>Shipping Information</h3>
                                    <div class="form-group">
                                        <label>Full Name</label>
                                        <input type="text" class="form-control" name="full_name" id="full_name">
                                    </div>

                                    <div class="form-group">
                                        <label>Street Address</label>
                                        <input type="text" class="form-control" name="address" id="address">
                                    </div>

                                    <div class="form-group">
                                        <label>Street Address Cont</label>
                                        <input type="text" class="form-control" name="address2" id="address2">
                                    </div>

                                    <div class="form-group">
                                        <label>City</label>
                                        <input type="text" class="form-control" name="city" id="city">
                                    </div>

                                    <div class="form-group">
                                        <label for="state">State</label>
                                        <select class="form-control" name="state" id="state">
                                            <option value="" title="">
                                                -- Please select --                    </option>
                                            <option value="1" title="Alabama">
                                                Alabama                    </option>
                                            <option value="2" title="Alaska">
                                                Alaska                    </option>
                                            <option value="3" title="American Samoa">
                                                American Samoa                    </option>
                                            <option value="4" title="Arizona">
                                                Arizona                    </option>
                                            <option value="5" title="Arkansas">
                                                Arkansas                    </option>
                                            <option value="6" title="Armed Forces Africa">
                                                Armed Forces Africa                    </option>
                                            <option value="7" title="Armed Forces Americas">
                                                Armed Forces Americas                    </option>
                                            <option value="8" title="Armed Forces Canada">
                                                Armed Forces Canada                    </option>
                                            <option value="9" title="Armed Forces Europe">
                                                Armed Forces Europe                    </option>
                                            <option value="10" title="Armed Forces Middle East">
                                                Armed Forces Middle East                    </option>
                                            <option value="11" title="Armed Forces Pacific">
                                                Armed Forces Pacific                    </option>
                                            <option value="12" title="California">
                                                California                    </option>
                                            <option value="13" title="Colorado">
                                                Colorado                    </option>
                                            <option value="14" title="Connecticut">
                                                Connecticut                    </option>
                                            <option value="15" title="Delaware">
                                                Delaware                    </option>
                                            <option value="16" title="District of Columbia">
                                                District of Columbia                    </option>
                                            <option value="17" title="Federated States Of Micronesia">
                                                Federated States Of Micronesia                    </option>
                                            <option value="18" title="Florida">
                                                Florida                    </option>
                                            <option value="19" title="Georgia">
                                                Georgia                    </option>
                                            <option value="20" title="Guam">
                                                Guam                    </option>
                                            <option value="21" title="Hawaii">
                                                Hawaii                    </option>
                                            <option value="22" title="Idaho">
                                                Idaho                    </option>
                                            <option value="23" title="Illinois">
                                                Illinois                    </option>
                                            <option value="24" title="Indiana">
                                                Indiana                    </option>
                                            <option value="25" title="Iowa">
                                                Iowa                    </option>
                                            <option value="26" title="Kansas">
                                                Kansas                    </option>
                                            <option value="27" title="Kentucky">
                                                Kentucky                    </option>
                                            <option value="28" title="Louisiana">
                                                Louisiana                    </option>
                                            <option value="29" title="Maine">
                                                Maine                    </option>
                                            <option value="30" title="Marshall Islands">
                                                Marshall Islands                    </option>
                                            <option value="31" title="Maryland">
                                                Maryland                    </option>
                                            <option value="32" title="Massachusetts">
                                                Massachusetts                    </option>
                                            <option value="33" title="Michigan">
                                                Michigan                    </option>
                                            <option value="34" title="Minnesota">
                                                Minnesota                    </option>
                                            <option value="35" title="Mississippi">
                                                Mississippi                    </option>
                                            <option value="36" title="Missouri">
                                                Missouri                    </option>
                                            <option value="37" title="Montana">
                                                Montana                    </option>
                                            <option value="38" title="Nebraska">
                                                Nebraska                    </option>
                                            <option value="39" title="Nevada">
                                                Nevada                    </option>
                                            <option value="40" title="New Hampshire">
                                                New Hampshire                    </option>
                                            <option value="41" title="New Jersey">
                                                New Jersey                    </option>
                                            <option value="42" title="New Mexico">
                                                New Mexico                    </option>
                                            <option value="43" title="New York">
                                                New York                    </option>
                                            <option value="44" title="North Carolina">
                                                North Carolina                    </option>
                                            <option value="45" title="North Dakota">
                                                North Dakota                    </option>
                                            <option value="46" title="Northern Mariana Islands">
                                                Northern Mariana Islands                    </option>
                                            <option value="47" title="Ohio">
                                                Ohio                    </option>
                                            <option value="48" title="Oklahoma">
                                                Oklahoma                    </option>
                                            <option value="49" title="Oregon">
                                                Oregon                    </option>
                                            <option value="50" title="Palau">
                                                Palau                    </option>
                                            <option value="51" title="Pennsylvania">
                                                Pennsylvania                    </option>
                                            <option value="52" title="Puerto Rico">
                                                Puerto Rico                    </option>
                                            <option value="53" title="Rhode Island">
                                                Rhode Island                    </option>
                                            <option value="54" title="South Carolina">
                                                South Carolina                    </option>
                                            <option value="55" title="South Dakota">
                                                South Dakota                    </option>
                                            <option value="56" title="Tennessee">
                                                Tennessee                    </option>
                                            <option value="57" title="Texas">
                                                Texas                    </option>
                                            <option value="58" title="Utah">
                                                Utah                    </option>
                                            <option value="59" title="Vermont">
                                                Vermont                    </option>
                                            <option value="60" title="Virgin Islands">
                                                Virgin Islands                    </option>
                                            <option value="61" title="Virginia">
                                                Virginia                    </option>
                                            <option value="62" title="Washington">
                                                Washington                    </option>
                                            <option value="63" title="West Virginia">
                                                West Virginia                    </option>
                                            <option value="64" title="Wisconsin">
                                                Wisconsin                    </option>
                                            <option value="65" title="Wyoming">
                                                Wyoming                    </option>
                                        </select>
                                    </div>


                                    <div class="form-group">
                                        <label>Zip</label>
                                        <input class="form-control" type="text" name="zipcode" id="zipcode">
                                    </div>




                                    <div class="form-group">
                                        <button href="#" class="btn btn-primary">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $("#order-form").submit(function(e) {
                e.preventDefault();
                $.ajax({
                    'headers' : {
                        'X-Api-Key' : '{{ $encrypted_api_key }}'
                    },
                    'type' : 'post',
                    'url' : '/api/v1/orders',
                    'data' : $("#order-form").serialize(),
                    'success' : function(msg) {
                        console.log(msg);
                    }
                });
            });
        });
    </script>
@endsection
