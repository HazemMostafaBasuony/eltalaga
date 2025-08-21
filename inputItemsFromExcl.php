<?php include('headAndFooter/head.php'); ?>
<div class="container p-4 h-100">
    <div class="row">
        <div class="header">
            <h1>استيراد وحفظ بيانات من ملف Excel</h1>
        </div>
        <div class="input">
            <input type="file" id="excelFileInput" accept=".xlsx, .xls">
            <button id="saveDataBtn" disabled>حفظ البيانات في قاعدة البيانات</button>
        </div>
        <div class="buttons">
            <button id="downloadTemplateBtn">تنزيل قالب Excel</button>
        </div>
        <div id="statusMessage"></div>

        <div id="output">
            <h2>البيانات المستوردة:</h2>
            <table id="dataTable"></table>`
        </div>
    </div>
</div>





</script>

<script src="js/addItemsFromExcl.js"></script>

<?php include('headAndFooter/footer.php'); ?>