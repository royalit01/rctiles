<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}
if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}
require '../db_connect.php';
$saRes = $mysqli->query("
    SELECT storage_area_id, storage_area_name
    FROM   storage_areas
    ORDER  BY storage_area_id ASC
    LIMIT  2
");
$saRows   = $saRes->fetch_all(MYSQLI_ASSOC);
$sa1      = $saRows[0]['storage_area_id'];
$sa2      = $saRows[1]['storage_area_id'];
$sa1Name  = $saRows[0]['storage_area_name'];
$sa2Name  = $saRows[1]['storage_area_name'];
$order_id = intval($_POST['order_id'] ?? 0);
if(!$order_id) die('Invalid');

$sql = "SELECT po.product_id,
       po.product_name,
       po.quantity AS ordered_qty,
       COALESCE(SUM(di.qty_delivered), 0) AS delivered_qty,
       COALESCE(sa1.quantity,0) AS qty1,
       COALESCE(sa1.pieces_per_packet,1) AS ppp1,
       COALESCE(sa2.quantity,0) AS qty2,
       COALESCE(sa2.pieces_per_packet,1) AS ppp2,
       COALESCE(SUM(ms.quantity_subtracted), 0) AS subtracted_qty
FROM pending_orders po
LEFT JOIN delivery_orders do2 ON do2.order_id = po.order_id
LEFT JOIN delivery_items di ON di.delivery_id = do2.delivery_id AND di.product_id = po.product_id
LEFT JOIN minus_stock ms ON ms.order_id = po.order_id AND ms.product_id = po.product_id
LEFT JOIN product_stock sa1 ON sa1.product_id = po.product_id AND sa1.storage_area_id = $sa1
LEFT JOIN product_stock sa2 ON sa2.product_id = po.product_id AND sa2.storage_area_id = $sa2
WHERE po.order_id = ?
GROUP BY po.product_id";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i',$order_id);
$stmt->execute();
$res = $stmt->get_result();
function fmtPkt($qty,$ppp){
    $pkt = intdiv($qty,$ppp);
    $pcs = $qty % $ppp;
    return $pkt.' pkt&nbsp;'.$pcs.' pc';
}
?>

<div class="modal-header">
  <h5 class="modal-title">Minus Stock – Order #<?= $order_id ?></h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="minusForm">
  
  <div class="modal-body">
    <input type="hidden" name="order_id" value="<?= $order_id ?>">
    <table class="table table-bordered align-middle">
      <thead class="table-secondary">
      <tr>
        <th>Product</th>
        <th>Qty Ordered</th>
        <th>Qty Delivered</th>
        <th>Qty Subtracted</th>
        <th>Remaining</th>
        <th>Stock <?= htmlspecialchars($sa1Name) ?> (ID <?= $sa1 ?>)</th>
        <th>Stock <?= htmlspecialchars($sa2Name) ?> (ID <?= $sa2 ?>)</th>
        <th>Storage Area</th>
        <th>Qty to Subtract</th>
      </tr>
      </thead>
      <tbody>
      <?php while($p = $res->fetch_assoc()): 
          $remaining = $p['ordered_qty'] - $p['delivered_qty'] - $p['subtracted_qty'];
          $remaining = max(0, $remaining);
          if ($remaining <= 0) continue; // Skip already completed items
      ?>
        <tr>
            <td><?= htmlspecialchars($p['product_name']) ?>
                <input type="hidden" name="product_id[]" value="<?= $p['product_id'] ?>">
            </td>
            <td><?= $p['ordered_qty'] ?></td>
            <td><?= $p['delivered_qty'] ?></td>
            <td><?= $p['subtracted_qty'] ?></td>
            <td><?= $remaining ?></td>
            <td><?= fmtPkt($p['qty1'], $p['ppp1']) ?></td>
            <td><?= fmtPkt($p['qty2'], $p['ppp2']) ?></td>
            <td>
                <select name="sa[]" class="form-select form-select-sm">
                    <option value="<?= $sa1 ?>"><?= $sa1 ?></option>
                    <option value="<?= $sa2 ?>"><?= $sa2 ?></option>
                </select>
            </td>
            <td>
                <input type="number"
                  min="1"
                  max="<?= $remaining ?>"
                  value="<?= $remaining ?>"
                  name="minus_qty[]"
                  class="form-control form-control-sm">
            </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button type="submit" class="btn btn-danger">Confirm Minus Stock</button>
  </div>
</form>

<script>
$('#minusForm').off('submit').on('submit',function(e){
    e.preventDefault();
    $.post('process_minus_stock.php', $(this).serialize(), function(resp){
        if(resp.trim()==='OK') {
            alert('Stock updated!');
            $('#minusModal').modal('hide');
            location.reload();
        } else {
            alert(resp);
        }
    });
});
</script>