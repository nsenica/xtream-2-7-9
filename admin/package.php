<?php
include "functions.php";
if (!isset($_SESSION['user_id'])) { header("Location: ./login.php"); exit; }
if (!$rPermissions["is_admin"]) { exit; }

if (isset($_POST["submit_package"])) {
    if (isset($_POST["edit"])) {
        $rArray = getPackage($_POST["edit"]);
        unset($rArray["id"]);
    } else {
        $rArray = Array("package_name" => "", "is_trial" => 0, "is_official" => 0, "trial_credits" => 0, "official_credits" => 0, "trial_duration_in" => "hours", "official_duration" => 1, "official_duration_in" => "years", "groups" => Array(), "bouquets" => Array(), "can_gen_mag" => 1, "only_mag" => 0, "output_formats" => Array(1,2,3), "is_isplock" => 0, "max_connections" => 1, "is_restreamer" => 0, "force_server_id" => 0, "only_e2" => 0, "can_gen_e2" => 1, "forced_country" => "", "lock_device" => 0);
    }
    if (strlen($_POST["package_name"]) == 0) {
        $_STATUS = 1;
    }
    foreach (Array("is_trial", "is_official", "can_gen_mag", "can_gen_e2", "only_mag", "only_e2", "lock_device", "is_restreamer") as $rSelection) {
        if (isset($_POST[$rSelection])) {
            $rArray[$rSelection] = 1;
            unset($_POST[$rSelection]);
        } else {
            $rArray[$rSelection] = 0;
        }
    }
    if (isset($_POST["groups"])) {
        $rArray["groups"] = Array();
        foreach ($_POST["groups"] as $rGroupID) {
            $rArray["groups"][] = intval($rGroupID);
        }
        $rArray["groups"] = "[".join(",", $rArray["groups"])."]";
        unset($_POST["groups"]);
    }
    if (isset($_POST["bouquets"])) {
        $rArray["bouquets"] = Array();
        foreach ($_POST["bouquets"] as $rBouquetID) {
            $rArray["bouquets"][] = intval($rBouquetID);
        }
        $rArray["bouquets"] = "[".join(",", $rArray["bouquets"])."]";
        unset($_POST["bouquets"]);
    }
    if (isset($_POST["output_formats"])) {
        $rArray["output_formats"] = Array();
        foreach ($_POST["output_formats"] as $rOutput) {
            $rArray["output_formats"][] = intval($rOutput);
        }
        $rArray["output_formats"] = "[".join(",", $rArray["output_formats"])."]";
        unset($_POST["output_formats"]);
    }
    if (!isset($_STATUS)) {
        foreach($_POST as $rKey => $rValue) {
            if (isset($rArray[$rKey])) {
                $rArray[$rKey] = $rValue;
            }
        }
        $rCols = "`".implode('`,`', array_keys($rArray))."`";
        foreach (array_values($rArray) as $rValue) {
            isset($rValues) ? $rValues .= ',' : $rValues = '';
            if (is_array($rValue)) {
                $rValue = json_encode($rValue);
            }
            if (is_null($rValue)) {
                $rValues .= 'NULL';
            } else {
                $rValues .= '\''.$db->real_escape_string($rValue).'\'';
            }
        }
        if (isset($_POST["edit"])) {
            $rCols = "`id`,".$rCols;
            $rValues = $_POST["edit"].",".$rValues;
        }
        $rQuery = "REPLACE INTO `packages`(".$rCols.") VALUES(".$rValues.");";
        if ($db->query($rQuery)) {
            if (isset($_POST["edit"])) {
                $rInsertID = intval($_POST["edit"]);
            } else {
                $rInsertID = $db->insert_id;
            }
            $_STATUS = 0;
        } else {
            $_STATUS = 2;
        }
        if (!isset($_GET["id"])) {
            $_GET["id"] = $rInsertID;
        }
    }
}

if (isset($_GET["id"])) {
    $rPackage = getPackage($_GET["id"]);
    if (!$rPackage) {
        exit;
    }
}

if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
        if ($rSettings["sidebar"]) { ?>
        <div class="content-page"><div class="content boxed-layout"><div class="container-fluid">
        <?php } else { ?>
        <div class="wrapper boxed-layout"><div class="container-fluid">
        <?php } ?>
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <a href="./packages.php"><li class="breadcrumb-item"><i class="mdi mdi-backspace"></i> Back to Packages</li></a>
                                </ol>
                            </div>
                            <h4 class="page-title"><?php if (isset($rPackage)) { echo "Edit"; } else { echo "Add"; } ?> Package</h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 
                <div class="row">
                    <div class="col-xl-12">
                        <?php if ((isset($_STATUS)) && ($_STATUS == 0)) { ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            Package operation was completed successfully.
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS > 0)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            There was an error performing this operation! Please check the form entry and try again.
                        </div>
                        <?php } ?>
                        <div class="card">
                            <div class="card-body">
                                <form action="./package.php<?php if (isset($_GET["id"])) { echo "?id=".$_GET["id"]; } ?>" method="POST" id="user_form">
                                    <?php if (isset($rPackage)) { ?>
                                    <input type="hidden" name="edit" value="<?=$rPackage["id"]?>" />
                                    <?php } ?>
                                    <div id="basicwizard">
                                        <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                            <li class="nav-item">
                                                <a href="#package-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline">Details</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#groups" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-account-group mr-1"></i>
                                                    <span class="d-none d-sm-inline">Groups</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#bouquets" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-flower-tulip mr-1"></i>
                                                    <span class="d-none d-sm-inline">Bouquets</span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content b-0 mb-0 pt-0">
                                            <div class="tab-pane" id="package-details">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="package_name">Package Name</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="package_name" name="package_name" value="<?php if (isset($rPackage)) { echo $rPackage["package_name"]; } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="is_trial">Is Trial</label>
                                                            <div class="col-md-2">
                                                                <input name="is_trial" id="is_trial" type="checkbox" <?php if (isset($rPackage)) { if ($rPackage["is_trial"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="trial_credits">Trial Credits</label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="trial_credits" name="trial_credits" onkeypress="return isNumberKey(event)" value="<?php if (isset($rPackage)) { echo $rPackage["trial_credits"]; } else { echo "0"; } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="trial_duration">Trial Duration</label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="trial_duration" name="trial_duration" value="<?php if (isset($rPackage)) { echo $rPackage["trial_duration"]; } else { echo "0"; } ?>">
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="trial_duration_in">Trial Duration In</label>
                                                            <div class="col-md-2">
                                                                <select name="trial_duration_in" id="trial_duration_in" class="form-control select2" data-toggle="select2">
                                                                    <?php foreach (Array("Hours", "Days") as $rOption) { ?>
                                                                    <option <?php if (isset($rPackage)) { if ($rPackage["trial_duration_in"] == strtolower($rOption)) { echo "selected "; } } ?>value="<?=strtolower($rOption)?>"><?=$rOption?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="is_official">Is Official</label>
                                                            <div class="col-md-2">
                                                                <input name="is_official" id="is_official" type="checkbox" <?php if (isset($rPackage)) { if ($rPackage["is_official"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="official_credits">Official Credits</label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="official_credits" name="official_credits" onkeypress="return isNumberKey(event)" value="<?php if (isset($rPackage)) { echo $rPackage["official_credits"]; } else { echo "0"; } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="official_duration">Official Duration</label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="official_duration" name="official_duration" value="<?php if (isset($rPackage)) { echo $rPackage["official_duration"]; } else { echo "0"; } ?>">
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="official_duration_in">Official Duration In</label>
                                                            <div class="col-md-2">
                                                                <select name="official_duration_in" id="official_duration_in" class="form-control select2" data-toggle="select2">
                                                                    <?php foreach (Array("Hours", "Days", "Months", "Years") as $rOption) { ?>
                                                                    <option <?php if (isset($rPackage)) { if ($rPackage["official_duration_in"] == strtolower($rOption)) { echo "selected "; } } ?>value="<?=strtolower($rOption)?>"><?=$rOption?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="can_gen_mag">Can Generate Mag</label>
                                                            <div class="col-md-2">
                                                                <input name="can_gen_mag" id="can_gen_mag" type="checkbox" <?php if (isset($rPackage)) { if ($rPackage["can_gen_mag"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="only_mag">Mag Only</label>
                                                            <div class="col-md-2">
                                                                <input name="only_mag" id="only_mag" type="checkbox" <?php if (isset($rPackage)) { if ($rPackage["only_mag"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="can_gen_e2">Can Generate Enigma</label>
                                                            <div class="col-md-2">
                                                                <input name="can_gen_e2" id="can_gen_e2" type="checkbox" <?php if (isset($rPackage)) { if ($rPackage["can_gen_e2"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="only_e2">Enigma Only</label>
                                                            <div class="col-md-2">
                                                                <input name="only_e2" id="only_e2" type="checkbox" <?php if (isset($rPackage)) { if ($rPackage["only_e2"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="lock_device">Lock STB Device</label>
                                                            <div class="col-md-2">
                                                                <input name="lock_device" id="lock_device" type="checkbox" <?php if (isset($rPackage)) { if ($rPackage["lock_device"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="is_restreamer">Can Restream</label>
                                                            <div class="col-md-2">
                                                                <input name="is_restreamer" id="is_restreamer" type="checkbox" <?php if (isset($rPackage)) { if ($rPackage["is_restreamer"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="max_connections">Max Connections</label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="max_connections" name="max_connections" value="<?php if (isset($rPackage)) { echo $rPackage["max_connections"]; } else { echo "1"; } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="output_formats">Access Output</label>
                                                            <div class="col-md-8">
                                                                <?php foreach (getOutputs() as $rOutput) { ?>
                                                                <div class="checkbox form-check-inline">
                                                                    <input data-size="large" type="checkbox" id="output_formats_<?=$rOutput["access_output_id"]?>" name="output_formats[]" value="<?=$rOutput["access_output_id"]?>"<?php if (isset($rPackage)) { if (in_array($rOutput["access_output_id"], json_decode($rPackage["output_formats"], True))) { echo " checked"; } } else { echo " checked"; } ?>>
                                                                    <label for="output_formats_<?=$rOutput["access_output_id"]?>"> <?=$rOutput["output_name"]?> </label>
                                                                </div>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="next list-inline-item float-right">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Next</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="groups">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <?php foreach (getMemberGroups() as $rGroup) { ?>
                                                            <div class="col-md-6">
                                                                <div class="custom-control custom-checkbox mt-1">
                                                                    <input type="checkbox" class="custom-control-input group-checkbox" id="group-<?=$rGroup["group_id"]?>" data-id="<?=$rGroup["group_id"]?>" name="groups[]" value="<?=$rGroup["group_id"]?>"<?php if(isset($rPackage)) { if(in_array($rGroup["group_id"], json_decode($rPackage["groups"], True))) { echo " checked"; } } ?>>
                                                                    <label class="custom-control-label" for="group-<?=$rGroup["group_id"]?>"><?=$rGroup["group_name"]?></label>
                                                                </div>
                                                            </div>
                                                            <?php } ?>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Previous</a>
                                                    </li>
                                                    <li class="list-inline-item">
                                                        <a href="javascript: void(0);" onClick="selectAll()" class="btn btn-secondary">Select All</a>
                                                        <a href="javascript: void(0);" onClick="selectNone()" class="btn btn-secondary">Deselect All</a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Next</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="bouquets">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <?php foreach (getBouquets() as $rBouquet) { ?>
                                                            <div class="col-md-6">
                                                                <div class="custom-control custom-checkbox mt-1">
                                                                    <input type="checkbox" class="custom-control-input bouquet-checkbox" id="bouquet-<?=$rBouquet["id"]?>" name="bouquets[]" value="<?=$rBouquet["id"]?>"<?php if(isset($rPackage)) { if(in_array($rBouquet["id"], json_decode($rPackage["bouquets"], True))) { echo " checked"; } } ?>>
                                                                    <label class="custom-control-label" for="bouquet-<?=$rBouquet["id"]?>"><?=$rBouquet["bouquet_name"]?></label>
                                                                </div>
                                                            </div>
                                                            <?php } ?>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Previous</a>
                                                    </li>
                                                    <li class="list-inline-item">
                                                        <a href="javascript: void(0);" onClick="selectAllBq()" class="btn btn-secondary">Select All</a>
                                                        <a href="javascript: void(0);" onClick="selectNoneBq()" class="btn btn-secondary">Deselect All</a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <input name="submit_package" type="submit" class="btn btn-primary" value="<?php if (isset($rPackage)) { echo "Edit"; } else { echo "Add"; } ?>" />
                                                    </li>
                                                </ul>
                                            </div>
                                        </div> <!-- tab-content -->
                                    </div> <!-- end #basicwizard-->
                                </form>

                            </div> <!-- end card-body -->
                        </div> <!-- end card-->
                    </div> <!-- end col -->
                </div>
            </div> <!-- end container -->
        </div>
        <!-- end wrapper -->
        <?php if ($rSettings["sidebar"]) { echo "</div>"; } ?>
        <!-- Footer Start -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12 copyright text-center"><?=getFooter()?></div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->

        <!-- Vendor js -->
        <script src="assets/js/vendor.min.js"></script>
        <script src="assets/libs/jquery-toast/jquery.toast.min.js"></script>
        <script src="assets/libs/jquery-nice-select/jquery.nice-select.min.js"></script>
        <script src="assets/libs/switchery/switchery.min.js"></script>
        <script src="assets/libs/select2/select2.min.js"></script>
        <script src="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
        <script src="assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
        <script src="assets/libs/clockpicker/bootstrap-clockpicker.min.js"></script>
        <script src="assets/libs/moment/moment.min.js"></script>
        <script src="assets/libs/daterangepicker/daterangepicker.js"></script>

        <!-- Plugins js-->
        <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>

        <!-- Tree view js -->
        <script src="assets/libs/treeview/jstree.min.js"></script>
        <script src="assets/js/pages/treeview.init.js"></script>
        <script src="assets/js/pages/form-wizard.init.js"></script>

        <!-- App js-->
        <script src="assets/js/app.min.js"></script>
        
        <script>
        (function($) {
          $.fn.inputFilter = function(inputFilter) {
            return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
              if (inputFilter(this.value)) {
                this.oldValue = this.value;
                this.oldSelectionStart = this.selectionStart;
                this.oldSelectionEnd = this.selectionEnd;
              } else if (this.hasOwnProperty("oldValue")) {
                this.value = this.oldValue;
                this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
              }
            });
          };
        }(jQuery));
        
        function selectAll() {
            $(".group-checkbox").each(function() {
                $(this).prop('checked', true);
            });
        }
        
        function selectNone() {
            $(".group-checkbox").each(function() {
                $(this).prop('checked', false);
            });
        }
        
        function selectAllBq() {
            $(".bouquet-checkbox").each(function() {
                $(this).prop('checked', true);
            });
        }
        
        function selectNoneBq() {
            $(".bouquet-checkbox").each(function() {
                $(this).prop('checked', false);
            });
        }
        
        function isNumberKey(evt) {
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
                return false;
            } else {
                return true;
            }
        }
        
        $(document).ready(function() {
            $('select.select2').select2({width: '100%'})
            $(".js-switch").each(function (index, element) {
                var init = new Switchery(element);
            });
            
            $(document).keypress(function(event){
                if (event.which == '13') {
                    event.preventDefault();
                }
            });

            $("#max_connections").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("#trial_duration").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("#official_duration").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("form").attr('autocomplete', 'off');
        });
        </script>
    </body>
</html>