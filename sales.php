<?php include('headAndFooter/head.php'); ?>

<?php

$invoiceType = isset($_GET['type']) ? $_GET['type'] : 'tekawy';
if ($invoiceType == 'tekawy') {
  $invoiceType = 'استلام';
  $customerId=0;

}elseif ($invoiceType == 'delivery') {
  $invoiceType = 'توصيل';
} else {
  $invoiceType = 'محلي';
  $customerId = -1;
}

$customerName = isset($_GET['customerName']) ? $_GET['customerName'] : 'مشتري عام';
$Phone = isset($_GET['phone']) ? $_GET['phone'] : '';
$Address = isset($_GET['address']) ? $_GET['address'] : '';
$PriceDelivery = isset($_GET['priceDelivery']) ? $_GET['priceDelivery'] : '0.00';
$qrAddress = isset($_GET['qrAddress']) ? $_GET['qrAddress'] : '';
$customerId = isset($_GET['customerId']) ? $_GET['customerId'] : -2;

// echo $customerId;
$invoiceId = isset($_SESSION['invoiceId']) ? $_SESSION['invoiceId'] : 0;
$serialShift = isset($_SESSION['serialShift']) ? $_SESSION['serialShift'] : 0;
$idShift = isset($_SESSION['idShift']) ? $_SESSION['idShift'] : 0;
$invoiceNumber = 'INV-'. date('YmdHis') . '--'. $idShift . '-' . $serialShift . $invoiceId ;  

// $invoiceNumber = 'INV-'. date('YmdHis') . '--'. $idShift . '-' . $serialShift;
// هههههههههههههاااااااااااام يجب التغيير و التحكم فى تسجيل الدخول
//$branch = "مدى الشام"; // تم

$idShift=$_SESSION['idShift'];
$serialDate = 1; // غير مطلوب
 // معرف الفاتورة

// echo $invoiceId;
?>


<div class="container">
  <!-- Invoice Section -->
  <section class="invoice-section">
    <div class="printInvoice">
      <div class="invoice-header">
        <div class="invoice-info">
          <div> <?php echo $invoiceNumber; ?></div>
        
          <div><i class="fas fa-calendar"></i> التاريخ: <strong id="current-date"><?php echo date('Y-m-d H:i'); ?></strong></div>
          <!-- <div><i class="fas fa-user"></i> الموظف: <strong><?php echo ($userName) ?> </strong></div> -->
        </div>
        <div class="customer-info" style="margin-top: 20px;">
          <div><i class="fas fa-user-tag"></i> نوع الفاتورة: <strong><?php echo htmlspecialchars($invoiceType); ?></strong>
          </div>
          <div><i class="fas fa-user-tag"></i> العميل: <strong><?php echo htmlspecialchars($customerName); ?></strong>
          </div>
        </div>
      </div>
      <div id="allInvoice" > 
        <!-- invoice -->
      </div>
    

      




      <div class="invoice-actions">
        <button onclick="endCash()" class="action-btn checkout-btn" id="checkout">
          
          <i class="fas fa-check-circle"></i> إنهاء الشراء
        </button>

        <button class="action-btn clear-btn" onclick="stateInvoice(<?php echo $invoiceId; ?>, 6);location.reload(true);"
          id="clear">
          <i class="fas fa-trash-alt"></i> مسح الفاتورة
        </button>
        
    

      </div>

    </div>
   </section>

  <!-- Products Section -->
  <section class="products-section">
    <!-- <h2 class="section-title"><i class="fas fa-boxes"></i> المنتجات</h2> -->
    <div class="categories" id="gruops">

      <!-- gruops -->
    </div>


    <div class="search-container">
      <i class="fas fa-search search-icon"></i>
      <input type="text" class="search-input" id="product-search" placeholder="ابحث عن منتج..." autocomplete="off">
      <div class="search-results" id="search-results" style="display: none;"></div>
    </div>
    <div class="products-grid" id="items">


      </div>
    </div>
  </section>




<div id="error" style="height: 500px; width: 100%;">
<div style="height:200px"></div>
    <!-- <button onclick=" sendToPrinter(85)" > go </button> -->
</div>



<!-- meduol commint -->
<!-- مودال التعليق -->
<div id="commintModal" class="w3-modal" style="display:none;">
  <div class="w3-modal-content w3-animate-top w3-padding" style="max-width:400px;">
    <span onclick="closeCommintModal()" class="w3-button w3-display-topright">&times;</span>
    <h4>إضافة/تعديل تعليق المنتج</h4>
    <input type="hidden" id="commintItemId">
    <input type="text" id="commintInput" class="w3-input w3-margin-bottom" placeholder="اكتب تعليق...">
    <div id="commintSuggestions" class="w3-margin-bottom"></div>
    <button class="w3-button w3-blue" onclick="saveCommint()">حفظ</button>
    <button class="w3-button w3-red" onclick="closeCommintModal()">اغلاق</button>
  </div>
</div>












<script>

  // test to connect slavePy  //4-7
async function sendToPrinter(num) {
    try {
        const response = await fetch('http://localhost:8080/print-number', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json' 
            },
            body: JSON.stringify({ 
                number: parseInt(num) // تم التعديل هنا لاستخدام 'number' بدل 'num'
            })
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(errorText);
        }
        
        const result = await response.json();
        console.log(result.message); // عرض رسالة التأكيد
        return result;
        
    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

  // 1
  getGruops();

  // 2
  getItemes("all");
  // 4

  getItemsInvoice(
    <?php echo json_encode($customerId); ?>,
    <?php echo json_encode($invoiceType); ?>,
    <?php echo json_encode($PriceDelivery); ?>
  );



  // getGruops 1111111111111111111111111111111111111111111111111

  function getGruops() {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (xhr.readyState == 4 && xhr.status == 200) {
        document.getElementById("gruops").innerHTML = xhr.responseText;


      }
    }

    xhr.open("GET", "sales/getGruops.php", true);

    xhr.send();


  }


  // getItemes 22222222222222222222222222222222222222222222222222
  function getItemes(nameGruop) {
    // alert(nameGruop); // إذا أردت التأكد من القيمة
    var xhr = new XMLHttpRequest();
    nameGruop = nameGruop ? nameGruop : "all";
    xhr.onreadystatechange = function () {
      if (xhr.readyState == 4 && xhr.status == 200) {
        document.getElementById("items").innerHTML = xhr.responseText;
      }
    }
    xhr.open("GET", "sales/getItemes.php?gruop=" + nameGruop, true);
    xhr.send();
  }





  // add to invoice 3333333333333333333333333333
  function addToInvoice(itemName, idi, price) {
    // إضافة مؤشر التحميل
    showLoadingSpinner();
    
    // إضافة تأثير على البطاقة
    const productCard = document.querySelector(`[data-id="${idi}"]`);
    if (productCard) {
      productCard.classList.add('loading');
    }

    var xhr = new XMLHttpRequest();
    var invoiceType = "<?php echo $invoiceType ?>";
    var customerName = "<?php echo $customerName ?>";
    var customerId = "<?php echo $customerId ?>";
    var invoiceType = "<?php echo $invoiceType ?>";
    var serialDate = "<?php echo $serialDate ?>";    
    var PriceDelivery = "<?php echo $PriceDelivery ?>";

    xhr.onreadystatechange = function () {
      if (xhr.readyState == 4 && xhr.status == 200) {
        getItemsInvoice("<?php echo $customerId; ?>", "<?php echo $invoiceType; ?>", "<?php echo $PriceDelivery; ?>");
        document.getElementById("error").innerHTML = xhr.responseText;
        
        // إخفاء مؤشر التحميل
        hideLoadingSpinner();
        
        // إزالة تأثير التحميل من البطاقة
        if (productCard) {
          productCard.classList.remove('loading');
        }
        
        // إظهار رسالة نجاح
        showToast('تم إضافة المنتج بنجاح!', 'success');
      }
    }
    xhr.open("GET", "sales/addToInvoice.php?itemName=" + encodeURIComponent(itemName)
      + "&idi=" + encodeURIComponent(idi)
      + "&invoiceType=" + encodeURIComponent(invoiceType)
      + "&customerName=" + encodeURIComponent(customerName)
      + "&customerId=" + encodeURIComponent(customerId)
      + "&serialDate=" + encodeURIComponent(serialDate)
      + "&PriceDelivery=" + encodeURIComponent(PriceDelivery)
      , true);

    xhr.send();
  }


  // getItemsInvoice 44444444444444444444444444444444
  function getItemsInvoice(customerId, invoiceType, PriceDelivery) {
    //  alert(PriceDelivery); // إذا أردت التأكد من القيمة
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (xhr.readyState == 4 && xhr.status == 200) {
        document.getElementById("allInvoice").innerHTML = xhr.responseText;
      }
    }
    xhr.open("GET", "sales/getItemsInvoice.php?customerId=" + encodeURIComponent(customerId) +
     "&invoiceType=" + encodeURIComponent(invoiceType) +
      "&PriceDelivery=" + PriceDelivery
      , true);
    xhr.send();
  }



  // removeItemFromInvoice 555555555555555555555555555555

  function removeItemFromInvoice(id_invoice_items) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (xhr.readyState == 4 && xhr.status == 200) {
        getItemsInvoice("<?php echo $customerId; ?>", "<?php echo $invoiceType; ?>", "<?php echo $PriceDelivery; ?>");
        // document.getElementById("allInvoice").innerHTML = xhr.responseText;
        // alert(id_invoice_items);

      }
    }
    xhr.open("GET", "sales/removeItemFromInvoice.php?id_invoice_items=" + id_invoice_items, true);
    xhr.send();
  }


  // addQuntity 6666666666666666666666666666666666666666666666
  function updateItemCount(id_invoice_items, action) {
    // alert(price);
    var xhr = new XMLHttpRequest();

    xhr.onreadystatechange = function () {
      if (xhr.readyState == 4 && xhr.status == 200) {
        //  document.getElementById("allInvoice").innerHTML = xhr.responseText;    
        getItemsInvoice("<?php echo $customerId; ?>", "<?php echo $invoiceType; ?>", "<?php echo $PriceDelivery; ?>");
        
          
      }
    }
    xhr.open("GET", "sales/updateItemCount.php?id_invoice_items=" + id_invoice_items + "&action=" + action, true);

    xhr.send();

  }
 


  async function printInvoice(invoiceId) {
    // الحصول على محتوى الفاتورة من الخادم
    const response = await fetch(`sales/generate_invoice.php?invoiceId=${invoiceId}`);
  
  }





  // sectionPrint 55552----------------------------------------------
  async function sectionPrintNumber(invoiceId, sectionPrint) {
    // الحصول على محتوى الفاتورة من الخادم
    const response = await fetch(`sales/sectionPrint.php?invoiceId=${invoiceId}&sectionPrint=${sectionPrint}`);
    
  }



  // 7777777777777777777777777777777777777777777777777777777

  function stateInvoice(invoiceId, state) {
    // alert(nameGruop); // إذا أردت التأكد من القيمة
    var xhr = new XMLHttpRequest();

    xhr.onreadystatechange = function () {
      if (xhr.readyState == 4 && xhr.status == 200) {
        
      }
    }
    xhr.open("GET", "sales/stateInvoice.php?invoiceId=" + invoiceId + "&state=" + state, true);
  
    xhr.send();
  }


  // 4 fun in 1 -******************************************
  // ******************************************************
  // ********************** Hazem ***********************
  // ******************************************************


  async function endCash() {
  try {
    // جلب رقم الفاتورة من العنصر المخفي
    
   let idInvoice = document.getElementById('invoiceId').value;
    
    

    // alert(idInvoice);

    await sendToPrinter(idInvoice);
    await stateInvoice(idInvoice, 2);

    window.location.replace('index.php');
    console.log("جميع المهام اكتملت بنجاح!");
  } catch (error) {
    alert("يرجى تشغيل برنامج الطباعة --- يوجد مشكلة فى برنامج الطباعه ", error);
    throw error;
  }
}















  // **********************************************
  // ********************************************
  // **********************************************
  // ***********    ***************     **********  
  // **********    ****************      ************
  // ************************************************
  //

  // وظائف تحسين تجربة المستخدم
  function setActiveCategory(element) {
    // إزالة الكلاس النشط من جميع الأزرار
    document.querySelectorAll('.category-btn').forEach(btn => {
      btn.classList.remove('active');
    });
    
    // إضافة الكلاس النشط للزر المحدد
    element.classList.add('active');
  }

  // تحسين البحث
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('product-search');
    
    if (searchInput) {
      searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const products = document.querySelectorAll('.product-card');
        
        products.forEach(product => {
          const productName = product.querySelector('.product-name').textContent.toLowerCase();
          if (productName.includes(searchTerm)) {
            product.style.display = 'flex';
          } else {
            product.style.display = 'none';
          }
        });
      });
    }
  });

  // دوال مساعدة للواجهة
  function showLoadingSpinner() {
    let spinner = document.querySelector('.loading-spinner');
    if (!spinner) {
      spinner = document.createElement('div');
      spinner.className = 'loading-spinner';
      spinner.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التحميل...';
      document.querySelector('.products-grid').appendChild(spinner);
    }
    spinner.style.display = 'block';
  }

  function hideLoadingSpinner() {
    const spinner = document.querySelector('.loading-spinner');
    if (spinner) {
      spinner.style.display = 'none';
    }
  }

  // تحسين دالة getItemes لإضافة انتقالات
  function getItemesWithAnimation(nameGruop) {
    const itemsContainer = document.getElementById('items');
    
    // إضافة تأثير الإخفاء
    itemsContainer.style.opacity = '0.5';
    
    // استدعاء الدالة الأصلية
    getItemes(nameGruop);
    
    // إضافة تأثير الإظهار بعد التحميل
    setTimeout(() => {
      itemsContainer.style.opacity = '1';
      const products = itemsContainer.querySelectorAll('.product-card');
      products.forEach((product, index) => {
        setTimeout(() => {
          product.classList.add('fade-in');
        }, index * 50);
      });
    }, 500);
  }

  // إجبار تحديث الجدول والـ CSS
  document.addEventListener('DOMContentLoaded', function() {
    // إضافة كلاسات للتأكد من تطبيق الـ CSS
    const invoiceTable = document.querySelector('.invoice-table');
    if (invoiceTable) {
      invoiceTable.classList.add('invoice-table-fixed');
    }
    
    const invoiceContainer = document.querySelector('.invoice-items-container');
    if (invoiceContainer) {
      invoiceContainer.classList.add('invoice-container-fixed');
    }
    
    // إعادة تحميل الجدول بعد تحميل الصفحة
    setTimeout(() => {
      const customerId = "<?php echo $customerId; ?>";
      const invoiceType = "<?php echo $invoiceType; ?>";
      const PriceDelivery = "<?php echo $PriceDelivery; ?>";
      if (customerId && invoiceType) {
        getItemsInvoice(customerId, invoiceType, PriceDelivery);
      }
    }, 1000);
    
    // إضافة CSS مباشرة في JavaScript للتأكد من التطبيق
    const style = document.createElement('style');
    style.textContent = `
      .invoice-table {
        background: rgba(0, 0, 0, 0.3) !important;
        color: white !important;
        border-radius: 8px !important;
        overflow: hidden !important;
        margin: 0 !important;
        width: 100% !important;
      }
      .invoice-table th {
        background: #2c3e50 !important;
        color: white !important;
        padding: 12px 8px !important;
        text-align: center !important;
      }
      .invoice-table td {
        padding: 10px 8px !important;
        text-align: center !important;
        color: white !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
      }
      .invoice-items-container {
        max-height: 400px !important;
        overflow-y: auto !important;
        background: rgba(255, 255, 255, 0.05) !important;
        border-radius: 10px !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        margin-bottom: 20px !important;
      }
    `;
    document.head.appendChild(style);
  });




  // open meduol 66666666666666666666666666666666666666
  // فتح المودال وجلب التعليقات السابقة
  function openCommintModal(itemId) {
    document.getElementById('commintItemId').value = itemId;
    document.getElementById('commintInput').value = '';
    document.getElementById('commintModal').style.display = 'block';
    // جلب التعليقات السابقة من XML
    fetch('sales/commints.xml')
      .then(res => res.text())
      .then(xmlText => {
        let parser = new DOMParser();
        let xml = parser.parseFromString(xmlText, "text/xml");
        let commints = Array.from(xml.getElementsByTagName('commint')).map(e => e.textContent);
        let html = '';
        commints.forEach(c => {
          html += `<button class="w3-button w3-light-grey w3-small w3-margin-bottom" onclick="addCommintToInput('${c.replace(/'/g, "\\'")}')">${c}</button> `;
          // html += `<button class="w3-button w3-light-grey w3-small w3-margin-bottom" onclick="document.getElementById('commintInput').value='${c.replace(/'/g,"\\'")}'">${c}</button> `;
        });
        document.getElementById('commintSuggestions').innerHTML = html;
      });
  }

  // إغلاق المودال
  function closeCommintModal() {
    document.getElementById('commintModal').style.display = 'none';
  }

  // اضافة التعليق على ما سبق ؟؟ التحديث
  function addCommintToInput(commint) {
    let input = document.getElementById('commintInput');
    if (input.value.trim() !== '') {
      input.value += ' - ' + commint;
    } else {
      input.value = commint;
    }
  }



  // حفظ التعليق
  function saveCommint() {
    let itemId = document.getElementById('commintItemId').value;
    let commint = document.getElementById('commintInput').value.trim();
    if (!commint) return alert('يرجى كتابة تعليق');
    // حفظ التعليق في قاعدة البيانات
    fetch('sales/saveCommint.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'itemId=' + encodeURIComponent(itemId) + '&commint=' + encodeURIComponent(commint)
    })
      .then(res => res.text())
      .then(msg => {
        // حفظ التعليق في XML إذا لم يكن مكرر
        fetch('sales/saveCommintToXml.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'commint=' + encodeURIComponent(commint)
        });
        closeCommintModal();
        // يمكنك تحديث الجدول أو عرض رسالة نجاح هنا


      });
  }


</script>

<?php include('headAndFooter/footer.php'); ?>