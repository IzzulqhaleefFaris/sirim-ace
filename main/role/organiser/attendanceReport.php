<?php
session_start();
include "../../../include/config.php";
include "../../../include/permissions.php";

require_manage_events();
?>

<!DOCTYPE html>
<html lang="en">
	<!--begin::Head-->
	<head>
		<base href="">
		<meta charset="utf-8" />
		<title>Attendance Report | ATTENDANCE</title>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="shortcut icon" href="../../../assets/media/logos/ace.png" />
		<!--begin::Fonts-->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
		<!--end::Fonts-->
		<!--begin::Global Stylesheets Bundle(used by all pages)-->
		<link href="../../../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
		<link href="../../../assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
		<!--end::Global Stylesheets Bundle-->
        <!--begin::Page Vendor Stylesheets(used by this page)-->
		<link href="../../../assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
		<link href="../../../assets/plugins/custom/prismjs/prismjs.bundle.css" rel="stylesheet" type="text/css" />
		<!--end::Page Vendor Stylesheets-->
	</head>
	<!--end::Head-->
	<?php
        $roleID = $_SESSION["roleId"];
        $userID = $_SESSION["userId"];

        if(!isset($_GET["SID"]))
        {
            $noSiri = 0;
        }else{
            $noSiri = $_GET["SID"];
        }

        $eventId = $_GET["eventId"];

        function ConvertSectoDay($n) 
        { 
            $day = floor($n / (24 * 3600)); 
        
            $n = ($n % (24 * 3600)); 
            $hour = $n / 3600; 
        
            $n %= 3600; 
            $minutes = $n / 60 ; 
        
            $n %= 60; 
            $seconds = $n; 
            
        echo ("$day hari $hour jam $minutes minit $seconds saat"); 
                
        }
    ?>

	<!--begin::Body-->
	<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed toolbar-tablet-and-mobile-fixed" style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">
		<!--begin::Main-->
		<!--begin::Root-->
		<div class="d-flex flex-column flex-root">
			<!--begin::Page-->
			<div class="page d-flex flex-row flex-column-fluid">
				<!--begin::Wrapper-->
				<div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
					<!--begin::Header-->
					<?php include "../../../include/header.php"; ?>
					<!--end::Header-->
					<!--begin::Content-->
					<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
						<!--begin::Toolbar-->
						<?php include "../../../include/toolbar.php"; ?>
						<!--end::Toolbar-->
						<!--begin::Post-->
						<div class="post d-flex flex-column-fluid" id="kt_post">
							<!--begin::Container-->
							<div id="kt_content_container" class="container-fluid">
								<!--begin::Section-->
								<div class="py-0">
                                    <div class="card shadow-sm">
										<div class="card-header">
											<h3 class="card-title">Laporan Kedatangan <?php echo $eventId;?></h3>
										</div>

										<form action="rondaReport.php" method="post" name="laporan" runat="server" style="clear: both;">
											<div class="card-body">
                                                <!--<div class="text-primary fs-7 fw-bold">&emsp;&emsp;Laporan Kedatangan <?php //echo $userID;?></div><br>-->
                                                <div class="row">
													<div class="mx-auto col-10 col-md-8 col-lg-10">
                                                        <!--begin::Table-->
                                                        <?php
															include 'include/config.php';
															date_default_timezone_set("Asia/Kuala_Lumpur");
															$Today = date("Y-m-d H:i:s");

                                                            if($roleID == "1"){
                                                               $checkAvail = "SELECT * FROM attendance WHERE eventId='" .  $eventId . "' GROUP BY paxId ORDER BY statusDate DESC";
                                                                } else {
                                                                $checkAvail = "SELECT * FROM attendance WHERE eventId='" .  $eventId . "' GROUP BY paxId ORDER BY statusDate DESC";
                                                                }
                                                                $recChk = mysqli_query($conn, $checkAvail);
                                                            
                                                                if ($datas = mysqli_fetch_assoc($recChk))
                                                                {
                                                                    $returnId = '1';
                                                                }else{
                                                                    $returnId = '0';
                                                                }
                                                            
                                                                if($returnId == '1'){
                                                                    $thispage = "attendanceReport.php";
    
                                                                    if(isset($_GET['start']))
                                                                    {
                                                                        $start = $_GET['start'];
                                                                    }else{
                                                                        $start = 0;
                                                                    }
    
                                                                    if(!($start > 0)) {
                                                                        $start = 0;
                                                                    }
    
                                                                    $eu = ($start -0);
    
                                                                    //Select & count table monitoring
                                                                    if($roleID == "1"){
                                                                        $query2 = "SELECT * FROM attendance WHERE eventId='" .  $eventId . "' GROUP BY paxId ORDER BY statusDate DESC";
                                                                    } else {
                                                                        $query2 = "SELECT * FROM attendance WHERE eventId='" .  $eventId . "' GROUP BY paxId ORDER BY statusDate DESC";
                                                                    }
                                                                    $result2 = mysqli_query($conn, $query2);
                                                                    $items = mysqli_num_rows($result2);
    
                                                                    $num = $items; // number of items in list
                                                                    $per_page = 20; // Number of items to show per page
                                                                    $showeachside = 10; //  Number of items to show either side of selected page
    
                                                                    if(empty($start))$start=0;  // Current start position

                                                                                    
                                                                //Select table monitoring
                                                                if($roleID == "1"){
                                                                    //echo $query = "SELECT * FROM attendance WHERE eventId = '" .  $eventId . "' ORDER BY statusDate ASC LIMIT $eu, $per_page";
                                                                    $query = "SELECT * FROM attendance WHERE eventId = '" .  $eventId . "' GROUP BY paxId ORDER BY statusDate ASC";
                                                                } 
                                                                $result = mysqli_query($conn, $query);
                                                                $items = mysqli_num_rows($result);
                                                            ?>

                                                            <?php $pgID = 0; ?>

                                                            <?php

                                                                if($start == "0")
                                                                {
                                                                    $intBil = 0;
                                                                }
                                                                else
                                                                {
                                                                    $intBil = $start;
                                                                }

                                                                $tarikh2 = "0";
                                                                ?>
																<table id="kt_datatable_example_5" class="table table-striped gy-3 gs-7 border rounded table-responsive">
																	<thead class="table-dark">
																		<tr class="fw-bolder fs-6 text-gray-800 px-7">
																			<th class="text-light font-weight-bold mr-4">Bil</th>
                                                                            <th class="text-light font-weight-bold mr-4">Name</th>
																			<th class="text-light font-weight-bold mr-4">Company</th>
                                                                                    <th class="text-light font-weight-bold mr-4">Check-in Time</th>
																		</tr>
																	</thead>
																	<tbody>
																		<?php while($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
																		{
                                                                            $intBil = $intBil + 1;
                                                                            $GetPaxName = $row['paxName'];      
                                                                            $GetPaxCom = $row['paxCom'];
                                                                            $GetStatusDate = $row['statusDate'];
                                                                            //$formatTarikhHadir = date("d/m/Y, l, H:i:s", strtotime($row['statusDate'];));
                                                                    
																			?>
																			<tr>
																				<td><?php echo $intBil; ?></td>
																				<td><?php echo $GetPaxName; ?></td>
																				<td><?php echo $GetPaxCom; ?></td>
																				<td><?php echo $GetStatusDate; ?></td>
																			</tr>
																		<?php } ?>
																	</tbody>
																</table>
                                                            <?php } ?>
                                                        <script>
                                                            var table = $('#kt_datatable_example_5').DataTable();
                                                            if ( ! table.data().count() ) {
                                                                alert( 'Empty table' );
                                                            }
                                                        </script>
                                                        <!--end::Table-->
                                                    </div>
                                                </div>
											</div>
										</form>
                                    </div>
								</div>
								<!--end::Section-->
							</div>
							<!--end::Container-->
						</div>
						<!--end::Post-->
					</div>
					<!--end::Content-->

					<!--begin::Footer-->
					<?php include "include/footer.php"; ?>
					<!--end::Footer-->
				</div>
				<!--end::Wrapper-->
			</div>
			<!--end::Page-->
		</div>
		<!--end::Root-->
		
		<!--begin::Scrolltop-->
		<?php include "include/scrolltop.php"; ?>
		<!--end::Scrolltop-->
		<!--end::Main-->

		<!--begin::Javascript-->
        <div>
            <!--begin::Global Javascript Bundle(used by all pages)-->
            <script src="assets/plugins/global/plugins.bundle.js"></script>
            <script src="assets/js/scripts.bundle.js"></script>
            <!--end::Global Javascript Bundle-->
            <!--begin::Page Custom Javascript(used by this page)-->
            <script src="assets/js/custom/widgets.js"></script>
            <script src="assets/js/custom/apps/chat/chat.js"></script>
            <script src="assets/js/custom/modals/create-app.js"></script>
            <script src="assets/js/custom/modals/upgrade-plan.js"></script>
            <script src="assets/js/custom/documentation/general/datatables/advanced.js"></script>
            <script src="assets/js/custom/documentation/documentation.js"></script>
            <!--end::Page Custom Javascript-->
            <!--begin::Page Vendors Javascript(used by this page)-->
            <script src="assets/plugins/custom/datatables/datatables.bundle.js"></script>
            <script src="assets/plugins/custom/prismjs/prismjs.bundle.js"></script>
            <!--end::Page Vendors Javascript-->
            <script>
                $(document).ready(function() {
                    $("#show_hide_password a").on('click', function(event) {
                        event.preventDefault();
                        if($('#show_hide_password input').attr("type") == "text"){
                            $('#show_hide_password input').attr('type', 'password');
                            $('#show_hide_password i').addClass( "fa-eye-slash" );
                            $('#show_hide_password i').removeClass( "fa-eye" );
                        }else if($('#show_hide_password input').attr("type") == "password"){
                            $('#show_hide_password input').attr('type', 'text');
                            $('#show_hide_password i').removeClass( "fa-eye-slash" );
                            $('#show_hide_password i').addClass( "fa-eye" );
                        }
                    });
                });
            </script>
        </div>
		<!--end::Javascript-->
	</body>
	<!--end::Body-->
</html>