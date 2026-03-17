<div class="col-sm-6 col-md-4 col-lg-3 p-b-35 isotope-item">
    <div class="block2">
        <div class="block2-pic hov-img0">
            <img src="images/<?php echo $row['Image']; ?>" alt="IMG-PRODUCT">
        </div>

        <div class="block2-txt flex-w flex-t p-t-14">
            <div class="block2-txt-child1 flex-col-l">
                <a href="product-detail.php?id=<?php echo $row['id']; ?>" class="stext-104 cl4">
                    <?php echo $row['Name']; ?>
                </a>

                <span class="stext-105 cl3">
                    ฿<?php echo $row['Price']; ?>
                </span>
            </div>
        </div>
    </div>
</div>