<div class="content-wrapper">
    <div class="page_container">
        <div class="box">
            <h3>Schools(<?php echo sizeof($Schools) ?>)
                <a href="javascript:;" class="btn btn-success pull-right" data-toggle="modal" data-target="#myModal">Add
                    New school</a>
            </h3>
            <div style="padding-top:20px; padding-left: 10px; padding-right: 20px;">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered example" style="width:100%">
                        <thead>
                            <tr>
                                <th>SNO</th>
                                <th>School Id</th>
                                <th>School Logo</th>
                                <th>School Name</th>
                                <th>School Principal</th>
                                <th>Affiliated To</th>
                                <th>Affiliation Number</th>
                                <th>School Address</th>
                                <th>Phone Number</th>
                                <th>Mobile Number</th>
                                <th>Email</th>
                                <th>Website</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="school-data">
                            <?php
                            $sno = 1;
                            foreach ($Schools as $school) {
                            ?>
                                <tr>
                                    <td><?php echo $sno ?></td>
                                    <td><?php echo $school['School Id']; ?></td>
                                    <td>
                                        <?php if (isset($school['Logo']) && filter_var($school['Logo'], FILTER_VALIDATE_URL)) : ?>
                                            <img src="<?php echo $school['Logo']; ?>" alt="School Logo" class="circular-image">
                                        <?php else : ?>
                                            <div class="no-logo">
                                                <span>No Logo</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $school['School Name']; ?></td>
                                    <td><?php echo $school['School Principal']; ?></td>
                                    <td><?php echo isset($school['Affiliated To']) ? $school['Affiliated To'] : 'N/A'; ?>
                                    </td>
                                    <td><?php echo isset($school['Affiliation Number']) ? $school['Affiliation Number'] : 'N/A'; ?>
                                    </td>
                                    <td><?php echo isset($school['Address']) ? $school['Address'] : 'N/A'; ?>
                                    </td>
                                    <td><?php echo isset($school['Phone Number']) ? $school['Phone Number'] : 'N/A'; ?></td>
                                    <td><?php echo isset($school['Mobile Number']) ? $school['Mobile Number'] : 'N/A'; ?>
                                    </td>
                                    <td><?php echo isset($school['Email']) ? $school['Email'] : 'N/A'; ?></td>
                                    <td><?php echo isset($school['Website']) ? $school['Website'] : 'N/A'; ?></td>
                                    <td>
                                        <a href="<?php echo base_url() . 'schools/delete_school/' . $school['School Id'] ?>"
                                            class="btn btn-danger"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                                        <a href="<?php echo base_url() . 'schools/edit_school/' . $school['School Id'] ?>"
                                            class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                                    </td>
                                </tr>
                            <?php
                                $sno++;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title text-center">Add New School</h4>
            </div>
            <div class="modal-body">
                <form action="<?php echo base_url() . 'schools/manage_school' ?>" id="add_school"
                    method="post" enctype="multipart/form-data">
                    <!-- School Basic Information -->
                    <div class="form-group">
                        <label>School ID</label>
                        <!-- <input type="text" name="School Id" id="school_id" class="form-control" required readonly
                            value="<?php echo isset($currentSchoolCount) ? $currentSchoolCount : ''; ?>"> -->
                        <input type="text" name="School Id" id="school_id" class="form-control" required readonly
                            value="<?php echo isset($currentSchoolCount) ? 'SCH' . str_pad($currentSchoolCount, 5, '0', STR_PAD_LEFT) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>School Name</label>
                        <input type="text" name="School Name" id="school_name" class="form-control" required
                            placeholder="Enter School Name">
                    </div>

                    <div class="form-group">
                        <label>School Principal</label>
                        <input type="text" name="School Principal" id="school_principal" class="form-control" required>
                    </div>


                    <div class="form-group">
                        <label>School Logo</label>
                        <input type="file" name="school_logo" id="school_logo" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>School Address</label>
                        <textarea name="Address" id="school_address" class="form-control" required
                            placeholder="Enter Address"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="Phone Number" id="phone_number" class="form-control" required
                            placeholder="Enter Phone Number">
                    </div>
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input type="text" name="Mobile Number" id="mobile_number" class="form-control" required
                            placeholder="Enter Mobile Number">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="Email" id="email" class="form-control" required
                            placeholder="Enter Email">
                    </div>
                    <div class="form-group">
                        <label>Website</label>
                        <input type="url" name="Website" id="website" class="form-control" required
                            placeholder="Enter Website">
                    </div>
                    <!-- Affiliation Details -->
                    <!-- <div class="form-group">
                        <label>Affiliated To</label>
                        <input type="text" name="Affiliated To" id="affiliated_to" class="form-control" required
                            placeholder="Enter Board">
                    </div> -->
                    <div class="form-group">
                        <label for="affiliated_to">Affiliated To</label>
                        <select name="Affiliated To" id="affiliated_to" class="form-control" required>
                            <option value="" disabled selected>Select Affiliated Board</option>
                            <optgroup label="All Boards">
                                <option value="Andhra Pradesh Board of Secondary Education">Andhra Pradesh Board of Secondary Education</option>
                                <option value="Board of Secondary Education, Assam">Board of Secondary Education, Assam</option>
                                <option value="Bihar School Examination Board">Bihar School Examination Board</option>
                                <option value="Chhattisgarh Board of Secondary Education">Chhattisgarh Board of Secondary Education</option>
                                <option value="Central Board of Secondary Education">Central Board of Secondary Education</option>
                                <option value="Council for the Indian School Certificate Examinations">Council for the Indian School Certificate Examinations</option>
                                <option value="National Institute of Open Schooling">National Institute of Open Schooling</option>
                                <option value="Goa Board of Secondary and Higher Secondary Education">Goa Board of Secondary and Higher Secondary Education</option>
                                <option value="Gujarat Secondary and Higher Secondary Education Board">Gujarat Secondary and Higher Secondary Education Board</option>
                                <option value="Board of School Education, Haryana">Board of School Education, Haryana</option>
                                <option value="Himachal Pradesh Board of School Education">Himachal Pradesh Board of School Education</option>
                                <option value="Jammu and Kashmir State Board of School Education">Jammu and Kashmir State Board of School Education</option>
                                <option value="Jharkhand Academic Council">Jharkhand Academic Council</option>
                                <option value="Karnataka Secondary Education Examination Board">Karnataka Secondary Education Examination Board</option>
                                <option value="Kerala Board of Public Examinations">Kerala Board of Public Examinations</option>
                                <option value="Madhya Pradesh Board of Secondary Education">Madhya Pradesh Board of Secondary Education</option>
                                <option value="Maharashtra State Board of Secondary and Higher Secondary Education">Maharashtra State Board of Secondary and Higher Secondary Education</option>
                                <option value="Board of Secondary Education, Manipur">Board of Secondary Education, Manipur</option>
                                <option value="Meghalaya Board of School Education">Meghalaya Board of School Education</option>
                                <option value="Mizoram Board of School Education">Mizoram Board of School Education</option>
                                <option value="Nagaland Board of School Education">Nagaland Board of School Education</option>
                                <option value="Board of Secondary Education, Odisha">Board of Secondary Education, Odisha</option>
                                <option value="Punjab School Education Board">Punjab School Education Board</option>
                                <option value="Board of Secondary Education, Rajasthan">Board of Secondary Education, Rajasthan</option>
                                <option value="Board of Secondary Education, Sikkim">Board of Secondary Education, Sikkim</option>
                                <option value="Tamil Nadu State Board of School Examination">Tamil Nadu State Board of School Examination</option>
                                <option value="Telangana State Board of Intermediate Education">Telangana State Board of Intermediate Education</option>
                                <option value="Tripura Board of Secondary Education">Tripura Board of Secondary Education</option>
                                <option value="Uttar Pradesh Madhyamik Shiksha Parishad">Uttar Pradesh Madhyamik Shiksha Parishad</option>
                                <option value="Uttarakhand Board of School Education">Uttarakhand Board of School Education</option>
                                <option value="West Bengal Board of Secondary Education">West Bengal Board of Secondary Education</option>
                                <option value="Other">Other</option>
                            </optgroup>

                        </select>
                    </div>


                    <div class="form-group">
                        <label>Affiliation Number</label>
                        <input type="text" name="Affiliation Number" id="affiliation_number" class="form-control"
                            required placeholder="Enter Affiliation Number">
                    </div>
                    <!-- Subscription Details -->
                    <div class="form-group">
                        <label>Subscription Plan</label>
                        <select name="subscription_plan" id="subscription_plan" class="form-control" required>
                            <option value="Premium Plan">Premium Plan</option>
                            <option value="Basic Plan">Basic Plan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Features</label>
                        <div id="features-container" class="form-control" style="height: auto; overflow: auto; padding: 10px;">
                            <div>
                                <input type="checkbox" id="select-all" />
                                <label for="select-all">Select All</label>
                            </div>
                            <div>
                                <input type="checkbox" name="features[]" id="feature1" value="School Management" />
                                <label for="feature1">School Management</label>
                            </div>
                            <div>
                                <input type="checkbox" name="features[]" id="feature2" value="Class Management" />
                                <label for="feature2">Class Management</label>
                            </div>
                            <div>
                                <input type="checkbox" name="features[]" id="feature3" value="Student Management" />
                                <label for="feature3">Student Management</label>
                            </div>
                            <div>
                                <input type="checkbox" name="features[]" id="feature4" value="Staff Management" />
                                <label for="feature4">Staff Management</label>
                            </div>
                            <div>
                                <input type="checkbox" name="features[]" id="feature5" value="Account Management" />
                                <label for="feature5">Account Management</label>
                            </div>
                            <div>
                                <input type="checkbox" name="features[]" id="feature6" value="Fees Management" />
                                <label for="feature6">Fees Management</label>
                            </div>

                            <div>
                                <input type="checkbox" name="features[]" id="feature7" value="Exam Management" />
                                <label for="feature8">Exam Management</label>
                            </div>

                            <div>
                                <input type="checkbox" name="features[]" id="feature7" value="Admin Management" />
                                <label for="feature7">Admin Management</label>
                            </div>
                        </div>
                    </div>


                    <div class="form-group">
                        <label>Subscription Duration (Months)</label>
                        <input type="number" name="subscription_duration" id="subscription_duration" class="form-control"
                            required placeholder="e.g., 12">
                    </div>
                    <div class="form-group">
                        <label>Last Payment Amount</label>
                        <input type="number" name="last_payment_amount" id="last_payment_amount" class="form-control"
                            required placeholder="Enter Last Payment Amount">
                    </div>
                    <div class="form-group">
                        <label>Last Payment Date</label>
                        <input type="date" name="last_payment_date" id="last_payment_date" class="form-control"
                            required>
                    </div>
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-control" required>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Debit Card">Debit Card</option>
                            <option value="Net Banking">Net Banking</option>
                            <option value="Cash">Cash</option>
                        </select>
                    </div>
                    <!-- Submit Button -->
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">Add School</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<style>
    .circular-image {
        width: 50px;
        /* Adjust width as needed */
        height: 50px;
        /* Adjust height as needed */
        border-radius: 50%;
        /* Makes the image circular */
        object-fit: cover;
        /* Ensures the image covers the circular area */
    }

    .no-logo {
        width: 50px;
        /* Same dimensions as the circular image */
        height: 50px;
        border-radius: 50%;
        background-color: rgba(0, 0, 0, 0.1);
        /* Light gray background */
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(0, 0, 0, 0.4);
        /* Slightly darker text */
        font-size: 12px;
        text-align: center;
        line-height: 1.2;
        /* Adjust line height */
    }

    .no-logo span {
        display: inline-block;
    }
</style>
