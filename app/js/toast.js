document.addEventListener('DOMContentLoaded', function () {
    var toastElement = document.getElementById('liveToast');
    if (toastElement) {
        var toast = new bootstrap.Toast(toastElement);
        toast.show();
    }
});
