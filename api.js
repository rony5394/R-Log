function openLoginPopup(width, height, url) {
    if (width == undefined){
        width = 500;
    }
    if (height == undefined){
        height = 400;
    }

    if (url == undefined){
        url = window.location.origin + "/loginApi/Example/api.php?openLoginPopup=true";
    }

    var left = (screen.width - width) / 2;
    var top = (screen.height - height) / 2;

    var windowName = "LoginPopup";
    var windowFeatures = "width=" + width + ",height=" + height +",left=" + left + ",top=" + top;

    // Open the new window
    var popupWindow = window.open(url, windowName, windowFeatures);
            
    // Focus on the new window
    if (popupWindow) {
        popupWindow.focus();
        }
    }
function loginWithFile() {
      const input = document.createElement('input');
      input.type = 'file';
      input.accept = '.rlog'; // You can specify the file type if needed

      input.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file) {
          const reader = new FileReader();

          reader.onload = function(e) {
            let content = e.target.result;
            console.log('File Content:', content);
            content = JSON.parse(content);
            document.getElementById("username").value = content.username;
            document.getElementById("password").value = content.password;
            document.getElementById("login").submit();
          };

          reader.readAsText(file);
        }
      });

      input.click();
}