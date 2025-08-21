<!-- <br><br><br><br><br><br><br> -->

</div>
</body>

<script>
  function w3_open() {
    document.getElementById("main").style.marginLeft = "25%";
    document.getElementById("mySidebar").style.width = "25%";
    document.getElementById("mySidebar").style.display = "block";
    document.getElementById("openNav").style.display = 'none';
  }
  function w3_close() {
    document.getElementById("main").style.marginLeft = "0%";
    document.getElementById("mySidebar").style.display = "none";
    document.getElementById("openNav").style.display = "inline-block";
  }


   // TOAST

    // عرض رسالة toast
    function showToast(message, type = 'success') {
        const toastContainer = document.createElement('div');
        toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '1050';

        toastContainer.innerHTML = `
    <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
        <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div> 
    </div>
    `;

        document.body.appendChild(toastContainer);
        const toast = new bootstrap.Toast(toastContainer.querySelector('.toast'));
        toast.show();

        toastContainer.querySelector('.toast').addEventListener('hidden.bs.toast', function() {
            document.body.removeChild(toastContainer);
        });
    }
</script>
<footer class="w3-container w3-bottom w3-theme w3-margin-top" style="opacity: 0.7;">
  <?php
  /*
  session_start();
  if ($_SESSION['userName']=="") {
    $ms=" welcome in fresh progrem  dv. eng : Hazem Basuony";
  } else {
    $userName0=$_SESSION['userName'];
    $Permission0=$_SESSION['Permission'];
    $ms="mr  :" .$userName0 ."    >>>>   ".$Permission0;
  }
  */
  $msFoorter = "welcome in Point progrem ..eng : Hazem.Basuony";
  ?>
  <h6><?php echo $msFoorter ?></h6>
</footer>