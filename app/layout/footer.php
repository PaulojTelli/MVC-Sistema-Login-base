  <!-- BOOTSTRAP 5 -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
    crossorigin="anonymous"></script>
  <!-- JQUERY -->
  <script src="<?php echo $root; ?>config/assets/vendors/js/jquery-3.6.4.min.js"></script>
  <!-- DATA TABLES JQUERY -->
  <script src="<?php echo $root; ?>config/assets/vendors/js/jquery.dataTables.min.js"></script>
  <!-- DATA TABLES BOOTSTRAP -->
  <script src="<?php echo $root; ?>config/assets/vendors/js/dataTables.bootstrap5.min.js"></script>
  <!-- DATE RANGE PICKERS -->
  <script type="text/javascript" src="<?php echo $root; ?>config/assets/vendors/js/moment.min.js"></script>
  <script type="text/javascript" src="<?php echo $root; ?>config/assets/vendors/js/daterangepicker.js"></script>
  <!-- JQUERY MASKMONEY -->
  <script type="text/javascript" src="<?php echo $root; ?>config/assets/vendors/js/jquery.maskMoney.min.js"></script>
  <!-- JS VANILLA -->
  <script src="<?php echo $root; ?>config/assets/js/script.js"></script>

  <script>
    $(document).ready(function () {
      $('#datatable').DataTable({
        paging: false,
        info: true,
        dom: 'Bfrtip',
        select: true,
        pageLength: 5,
        recordsTotal: 10,
      });
    });
  </script>
</body>

</html>
