// location.js

function getLocation(callback) {
  if (!navigator.geolocation) {
    alert("المتصفح لا يدعم تحديد الموقع. بعض الوظائف لن تعمل.");
    callback(null, "Geolocation not supported");
    return;
  }

  navigator.geolocation.getCurrentPosition(
    function (position) {
      const lat = position.coords.latitude;
      const lng = position.coords.longitude;
      callback({ lat, lng }, null); // success
    },
    function (error) {
      if (error.code === error.PERMISSION_DENIED) {
        const container = document.getElementById('locationWarning');
        if (!container) {
          const warningDiv = document.createElement('div');
          warningDiv.id = 'locationWarning';
          warningDiv.style.background = '#fff3cd';
          warningDiv.style.padding = '20px';
          warningDiv.style.border = '1px solid #ffeeba';
          warningDiv.style.margin = '10px 0';
          warningDiv.style.textAlign = 'center';

          const message = document.createElement('p');
          message.textContent = "تم رفض طلب تحديد الموقع. البرنامج يحتاج إذن الموقع للعمل.";
          warningDiv.appendChild(message);

          const retryBtn = document.createElement('button');
          retryBtn.textContent = "حاول مرة أخرى";
          retryBtn.style.padding = '10px 20px';
          retryBtn.style.cursor = 'pointer';
          retryBtn.addEventListener('click', function () {
            warningDiv.remove();
            getLocation(callback); // إعادة المحاولة
          });

          warningDiv.appendChild(retryBtn);
          document.body.prepend(warningDiv);
        }
        callback(null, "Permission denied");
      } else {
        alert("حدث خطأ أثناء الحصول على الموقع. حاول مرة أخرى.");
        callback(null, error);
      }
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
  );
}
