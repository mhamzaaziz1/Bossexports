<?php
// Warranty start date (handing over / delivery date)
$start_date = !empty($warranty['delivery_date'])
    ? $warranty['delivery_date']
    : date('Y-m-d');

// Warranty end date
$end_date = $warranty['guarantee_period'] ?? null;

$warranty_text = '';

if ($end_date) {
    $start = new DateTime($start_date);
    $end   = new DateTime($end_date);
    $diff  = $start->diff($end);

    if ($diff->y > 0) {
        $warranty_text = $diff->y . ' Year' . ($diff->y > 1 ? 's' : '');
    } elseif ($diff->m > 0) {
        $warranty_text = $diff->m . ' Month' . ($diff->m > 1 ? 's' : '');
    } else {
        $warranty_text = $diff->d . ' Day' . ($diff->d > 1 ? 's' : '');
    }
}


$CI = &get_instance();

$invoice = $CI->db->where('id',$warranty['invoice_id'])->get('tblinvoices')->row();

$project_name='';
if(!empty($invoice->project_id)){
    $project = $CI->db->where('id',$invoice->project_id)->get('tblprojects')->row();
    $project_name = $project->name ?? '';
}


?>

<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: dejavusans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .title {
            text-align: center;
            font-family: 'Times New Roman', Times, serif;
            color: #0c5191;
            font-size: 22px;
            /* font-weight: bold; */
            margin-top: 0px;
            margin: 10px 0 15px 0;
            display: inline-block;
            text-decoration-line: underline;
        }


        .intro-text {
            margin-bottom: 12px;
            font-family: 'Times New Roman', Times, serif;
            /*font-size: large;*/
            margin-left: 0;
            margin-right: 0;
            padding-right: 0;
        }

        .info-table {
            width: 100%;
            margin-bottom: 10px;
            margin-left: 0;
            margin-right: 0;
            padding-left: 0;
            padding-right: 0;
        }

        .info-table td {
            padding: 4px;
        }

        .terms {
            margin-top: 5px;
            font-size: 12px;
            font-family: 'Times New Roman', Times, serif;
        }

        .signature {
            margin-top: 30px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 15px;
        }

        .content {
            padding: 0;
            margin: 0;
        }

        /* Table styling */
        .items-table {
            width: 100%;
            border-collapse: collapse;

        }

        .items-table th {
            font-family: 'Times New Roman', Times, serif;
            background-color: #0c5191;
            color: #ffffff;
            text-align: center;
            line-height: 1.6;
            vertical-align: left;
            height: 25px;
            padding: 0 12px;
        }


        .items-table td {
            font-family: 'Times New Roman', Times, serif;
            /* border: 1px solid #000; */
            padding: 7px;
            margin-top: 30px;
            vertical-align: middle;
            text-align: center;
            font-size: 12px;
            word-wrap: break-word;
            line-height: 2.4;
            /* prevent overflow */
        }



        /* Utility classes */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }
    </style>

</head>

<body>
    <div class="content">

        <table class="items-table">
            <div class="title">
                WARRANTY & HANDING OVER LETTER
            </div>


            <p style=" font-family: 'Times New Roman', Times, serif;"><Strong>Client :</Strong> <?php echo get_company_name($warranty['customer_code']); ?></p>
            <p style=" font-family: 'Times New Roman', Times, serif;"><strong>Start Date :</strong> <?php echo _d(date('Y-m-d')); ?></p>
            <p style=" font-family: 'Times New Roman', Times, serif;"><strong>End Date : </strong><?php echo _d($warranty['guarantee_period']); ?></p>
            <p style=" font-family: 'Times New Roman', Times, serif;"><strong>Project Name :</strong> <?php echo $project_name; ?></p>



            <!-- <p style="font-family: 'Times New Roman', Times, serif; font-size:larger;"><strong>Date:</strong> <?php //echo _d(date('Y-m-d')); 
                                                                                                                    ?></p>
            <p style="font-family: 'Times New Roman', Times, serif;font-size:larger;"><strong>Client:</strong> M/s. <?php //echo get_company_name($warranty['customer_code']); 
                                                                                                                    ?></p> -->

            <!-- <div class="intro-text">This is to certify that below mentioned item(s) have been supplied, installed and handed over in good working condition to
                <strong>M/s. <?php //echo get_company_name($warranty['customer_code']); 
                                ?></strong>. -->
            <!-- </div>   -->

            <?php if (!empty($content)) { ?>
                <?php echo $content; ?>
            <?php } ?>

            <br>

        </table>

        <br>



        <!-- <div class="terms">We warranty the above item for a period of <strong><?php echo $warranty_text; ?></strong> from the date of handing over. The maintenance guarantee will commence from <strong><?php echo _d($start_date); ?></strong> and remain valid until <strong><?php echo _d($warranty['guarantee_period']); ?></strong>. This certificate is issued for maintenance guarantee only and does not cover physical accidents, vandalism, fire, or damage due to natural calamities.
        </div> -->


        <div class="signature">
            <strong>Authorized Signatory</strong>
        </div>
        <img src="<?php echo base_url('modules/warranty_management/assets/pdf/Stamp.png'); ?>" alt="Stamp Image" style="width: 90px; height 90px;">
    </div>

</body>

</html>