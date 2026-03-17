<?php
// เชื่อมต่อ Database และดึงข้อมูลตะกร้าของ User คนนั้นๆ
$query = "SELECT * FROM cart WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $query);

while($row = mysqli_fetch_array($result)) {
?>
    <tr class="table_row" data-id="<?php echo $row['id']; ?>">
        <td class="column-1">
            <div class="how-itemcart1 btn-remove" data-id="<?php echo $row['id']; ?>">
                <img src="<?php echo $row['product_img']; ?>" alt="IMG">
            </div>
        </td>
        <td class="column-2"><?php echo $row['product_name']; ?></td>
        <td class="column-3">฿ <?php echo number_format($row['price'], 2); ?></td>
        <td class="column-4">
            <input class="num-product" type="number" value="<?php echo $row['qty']; ?>">
        </td>
        <td class="column-5">฿ <?php echo number_format($row['price'] * $row['qty'], 2); ?></td>
    </tr>
<?php } ?>