<?php

    /*!
     * ifsoft.co.uk
     *
     * http://ifsoft.com.ua, http://ifsoft.co.uk
     * raccoonsquare@gmail.com
     *
     * Copyright 2012-2019 Demyanchuk Dmitry (raccoonsquare@gmail.com)
     */

     include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");

    if (!admin::isSession()) {

        header("Location: /admin/login.php");
        exit;
    }

    $stats = new stats($dbo);

    $stream = new stream($dbo);
    $stream->setRequestFromAdmin(true);

    $inbox_all = $stream->getAllCount();
    $inbox_loaded = 0;

    if (!empty($_POST)) {

        $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;
        $loaded = isset($_POST['loaded']) ? $_POST['loaded'] : 0;

        $itemId = helper::clearInt($itemId);
        $loaded = helper::clearInt($loaded);

        $result = $stream->get($itemId);

        $inbox_loaded = count($result['items']);

        $result['inbox_loaded'] = $inbox_loaded + $loaded;
        $result['inbox_all'] = $inbox_all;

        if ($inbox_loaded != 0) {

            ob_start();

            foreach ($result['items'] as $key => $value) {

                draw($value);
            }

            $result['html'] = ob_get_clean();

            if ($result['inbox_loaded'] < $inbox_all) {

                ob_start();

                ?>

                    <a href="javascript:void(0)" onclick="Stream.moreItems('<?php echo $result['itemId']; ?>'); return false;">
                        <button type="button" class="btn  btn-info footable-show">View More</button>
                    </a>

                <?php

                $result['html2'] = ob_get_clean();
            }
        }

        echo json_encode($result);
        exit;
    }

    $page_id = "photos_moderation";

    $css_files = array("mytheme.css");
    $page_title = "Photos Moderation | Admin Panel";

    include_once($_SERVER['DOCUMENT_ROOT']."/common/admin_header.inc.php");
?>

<body class="fix-header fix-sidebar card-no-border">

    <div id="main-wrapper">

        <?php

            include_once($_SERVER['DOCUMENT_ROOT']."/common/admin_topbar.inc.php");
        ?>

        <?php

            include_once($_SERVER['DOCUMENT_ROOT']."/common/admin_sidebar.inc.php");
        ?>

        <div class="page-wrapper">

            <div class="container-fluid">

                <div class="row page-titles">
                    <div class="col-md-5 col-8 align-self-center">
                        <h3 class="text-themecolor">Dashboard</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/admin/main.php">Home</a></li>
                            <li class="breadcrumb-item active">Photos Moderation</li>
                        </ol>
                    </div>
                </div>

                <?php

                    include_once($_SERVER['DOCUMENT_ROOT']."/common/admin_banner.inc.php");
                ?>

                <?php

                    $result = $stream->get(0);

                    $inbox_loaded = count($result['items']);

                    if ($inbox_loaded != 0) {

                        ?>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title m-b-0">Photos Moderation</h4>
                                    </div>
                                    <div class="card-body collapse show">
                                        <div class="table-responsive">
                                            <table class="table product-overview">
                                                <thead>
                                                <tr>
                                                    <th colspan="2">From User</th>
                                                    <th>Image</th>
                                                    <th>Text</th>
                                                    <th>Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                                </thead>
                                                <tbody class="data-table">
                                                    <?php

                                                        foreach ($result['items'] as $key => $value) {

                                                            draw($value);
                                                        }

                                                    ?>
                                                </tbody>

                                            </table>
                                        </div>
                                    </div>

                                    <?php

                                        if ($inbox_loaded >= 20) {

                                            ?>

                                            <div class="card-body more-items loading-more-container">
                                                <a href="javascript:void(0)" onclick="Stream.moreItems('<?php echo $result['itemId']; ?>'); return false;">
                                                    <button type="button" class="btn  btn-info footable-show">View More</button>
                                                </a>
                                            </div>

                                            <?php
                                        }
                                    ?>

                                </div>
                            </div>
                        </div>

                        <?php

                    } else {

                        ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h4 class="card-title">List is empty.</h4>
                                            <p class="card-text">This means that there is no data to display :)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                    }
                ?>

            </div> <!-- End Container fluid  -->

            <?php

                include_once($_SERVER['DOCUMENT_ROOT']."/common/admin_footer.inc.php");
            ?>

        <script type="text/javascript">

            var inbox_all = <?php echo $inbox_all; ?>;
            var inbox_loaded = <?php echo $inbox_loaded; ?>;

            window.Stream || ( window.Stream = {} );

            Stream.reject = function (offset, accessToken, fromUserId) {

                $.ajax({
                    type: 'GET',
                    url: '/admin/photo_reject.php/?id=' + offset  + '&access_token=' + accessToken + '&fromUserId=' + fromUserId,
                    data: 'itemId=' + offset + "&access_token=" + accessToken + "&fromUserId=" + fromUserId,
                    timeout: 30000,
                    success: function(response) {

                        $('tr.data-item[data-id=' + offset + ']').remove();
                    },
                    error: function(xhr, type){

                    }
                });
            };

            Stream.approve = function (offset, accessToken) {

                $.ajax({
                    type: 'GET',
                    url: '/admin/photo_approve.php/?id=' + offset  + '&access_token=' + accessToken,
                    data: 'itemId=' + offset + "&access_token=" + accessToken,
                    timeout: 30000,
                    success: function(response) {

                        $('tr.data-item[data-id=' + offset + ']').remove();
                    },
                    error: function(xhr, type){

                    }
                });
            };

            Stream.moreItems = function (offset) {

                $('div.loading-more-container').hide();

                $.ajax({
                    type: 'POST',
                    url: '/admin/moderation_photos.php',
                    data: 'itemId=' + offset + "&loaded=" + inbox_loaded,
                    dataType: 'json',
                    timeout: 30000,
                    success: function(response){

                        if (response.hasOwnProperty('html2')){

                            $("div.loading-more-container").html("").append(response.html2).show();
                        }

                        if (response.hasOwnProperty('html')){

                            $("tbody.data-table").append(response.html);
                        }

                        inbox_loaded = response.inbox_loaded;
                        inbox_all = response.inbox_all;
                    },
                    error: function(xhr, type){

                        $('div.loading-more-container').show();
                    }
                });
            };

        </script>

        </div> <!-- End Page wrapper  -->
    </div> <!-- End Wrapper -->

</body>

</html>

<?php

    function draw($item)
    {
        ?>

            <tr class="data-item" data-id="<?php echo $item['id']; ?>">

                <td style="width:50px;">

                    <?php

                        if (strlen($item['fromUserPhoto']) != 0) {

                            ?>
                                <span class="round" style="background-size: cover; background-image: url(<?php echo $item['fromUserPhoto']; ?>)"></span>
                            <?php

                        } else {

                            ?>
                                <span class="round" style="background-size: cover; background-image: url(/img/profile_default_photo.png)"></span>
                            <?php
                        }
                    ?>
                </td>
                <td>
                    <h6><a href="/admin/profile.php?id=<?php echo $item['fromUserId']; ?>"><?php echo $item['fromUserFullname']; ?></a></h6>
                    <small class="text-muted">@<?php echo $item['fromUserUsername']; ?></small>
                </td>
                <td>

                    <?php

                        if (strlen($item['imgUrl']) != 0) {

                            ?>
                                <img src="<?php echo $item['imgUrl']; ?>" alt="iMac" width="80">
                            <?php

                        } else {

                            ?>
                            <h6>-</h6>
                            <?php
                        }
                    ?>
                </td>
                <td>
                    <?php

                        if (strlen($item['comment']) != 0) {

                            ?>
                                <h6><?php echo $item['comment']; ?></h6>
                            <?php

                        } else {

                            ?>
                                <h6>-</h6>
                            <?php
                        }
                    ?>
                </td>
                <td>
                    <h6><?php echo $item['timeAgo']; ?></h6>
                </td>
                <td>
                    <a href="javascript:void(0)" onclick="Stream.reject('<?php echo $item['id']; ?>', '<?php echo admin::getAccessToken(); ?>', '<?php echo $item['fromUserId']; ?>'); return false;" class="text-inverse" title="" data-toggle="tooltip" data-original-title="Reject"><i class="ti-trash"></i> Reject</a>
                    <span> | </span>
                    <a href="javascript:void(0)" onclick="Stream.approve('<?php echo $item['id']; ?>', '<?php echo admin::getAccessToken(); ?>'); return false;" class="text-inverse" title="" data-toggle="tooltip" data-original-title="Approve"><i class="ti-check"></i> Approve</a>
                </td>
            </tr>

        <?php
    }