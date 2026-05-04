var z = document.querySelectorAll("[data-toast]");
if (typeof Toastify !== "undefined") {
    Array.from(z).forEach(function (a) {
        a.addEventListener("click", function () {
            var e = {}, t = a.attributes;

            if (t["data-toast-text"]) e.text = t["data-toast-text"].value.toString();
            if (t["data-toast-gravity"]) e.gravity = t["data-toast-gravity"].value.toString();
            if (t["data-toast-position"]) e.position = t["data-toast-position"].value.toString();
            if (t["data-toast-className"]) e.className = t["data-toast-className"].value.toString();
            if (t["data-toast-duration"]) e.duration = t["data-toast-duration"].value.toString();
            if (t["data-toast-close"]) e.close = t["data-toast-close"].value.toString();
            if (t["data-toast-style"]) e.style = t["data-toast-style"].value.toString();
            if (t["data-toast-offset"]) e.offset = t["data-toast-offset"];

            Toastify({
                newWindow: true,
                text: e.text,
                gravity: e.gravity,
                position: e.position,
                className: "bg-" + e.className,
                stopOnFocus: true,
                offset: { x: e.offset ? 50 : 0, y: e.offset ? 10 : 0 },
                duration: e.duration,
                close: e.close === "close",
                style: e.style === "style"
                    ? { background: "linear-gradient(to right, var(--vz-success), var(--vz-primary))" }
                    : ""
            }).showToast();
        });
    });
}

z = document.querySelectorAll("[data-choices]");
if (typeof Choices !== "undefined") {
    Array.from(z).forEach(function (e) {
        var t = {}, a = e.attributes;

        if (a["data-choices-groups"]) t.placeholderValue = "This is a placeholder set in the config";
        if (a["data-choices-search-false"]) t.searchEnabled = false;
        if (a["data-choices-search-true"]) t.searchEnabled = true;
        if (a["data-choices-removeItem"]) t.removeItemButton = true;
        if (a["data-choices-sorting-false"]) t.shouldSort = false;
        if (a["data-choices-sorting-true"]) t.shouldSort = true;
        if (a["data-choices-multiple-remove"]) t.removeItemButton = true;
        if (a["data-choices-limit"]) t.maxItemCount = a["data-choices-limit"].value.toString();
        if (a["data-choices-editItem-true"]) t.maxItemCount = true;
        if (a["data-choices-editItem-false"]) t.maxItemCount = false;
        if (a["data-choices-text-unique-true"]) t.duplicateItemsAllowed = false;
        if (a["data-choices-text-disabled-true"]) t.addItems = false;

        if (a["data-choices-text-disabled-true"]) {
            new Choices(e, t).disable();
        } else {
            new Choices(e, t);
        }
    });
}

z = document.querySelectorAll("[data-provider]");
if (typeof flatpickr !== "undefined") {
    Array.from(z).forEach(function (e) {
        var t, a, n;

        if (e.getAttribute("data-provider") === "flatpickr") {
            n = e.attributes;
            t = {};
            t.disableMobile = true;

            if (n["data-date-format"]) t.dateFormat = n["data-date-format"].value.toString();
            if (n["data-enable-time"]) {
                t.enableTime = true;
                t.dateFormat = n["data-date-format"].value.toString() + " H:i";
            }
            if (n["data-altFormat"]) {
                t.altInput = true;
                t.altFormat = n["data-altFormat"].value.toString();
            }
            if (n["data-minDate"]) {
                t.minDate = n["data-minDate"].value.toString();
                t.dateFormat = n["data-date-format"].value.toString();
            }
            if (n["data-maxDate"]) {
                t.maxDate = n["data-maxDate"].value.toString();
                t.dateFormat = n["data-date-format"].value.toString();
            }
            if (n["data-deafult-date"]) {
                t.defaultDate = n["data-deafult-date"].value.toString();
                t.dateFormat = n["data-date-format"].value.toString();
            }
            if (n["data-multiple-date"]) {
                t.mode = "multiple";
                t.dateFormat = n["data-date-format"].value.toString();
            }
            if (n["data-range-date"]) {
                t.mode = "range";
                t.dateFormat = n["data-date-format"].value.toString();
            }
            if (n["data-inline-date"]) {
                t.inline = true;
                t.defaultDate = n["data-deafult-date"].value.toString();
                t.dateFormat = n["data-date-format"].value.toString();
            }
            if (n["data-disable-date"]) {
                a = [];
                a.push(n["data-disable-date"].value);
                t.disable = a.toString().split(",");
            }
            if (n["data-week-number"]) {
                t.weekNumbers = true;
            }

            flatpickr(e, t);
        }

        if (e.getAttribute("data-provider") === "timepickr") {
            a = {};
            n = e.attributes;

            if (n["data-time-basic"]) {
                a.enableTime = true;
                a.noCalendar = true;
                a.dateFormat = "H:i";
            }
            if (n["data-time-hrs"]) {
                a.enableTime = true;
                a.noCalendar = true;
                a.dateFormat = "H:i";
                a.time_24hr = true;
            }
            if (n["data-min-time"]) {
                a.enableTime = true;
                a.noCalendar = true;
                a.dateFormat = "H:i";
                a.minTime = n["data-min-time"].value.toString();
            }
            if (n["data-max-time"]) {
                a.enableTime = true;
                a.noCalendar = true;
                a.dateFormat = "H:i";
                a.maxTime = n["data-max-time"].value.toString();
            }
            if (n["data-default-time"]) {
                a.enableTime = true;
                a.noCalendar = true;
                a.dateFormat = "H:i";
                a.defaultDate = n["data-default-time"].value.toString();
            }
            if (n["data-time-inline"]) {
                a.enableTime = true;
                a.noCalendar = true;
                a.defaultDate = n["data-time-inline"].value.toString();
                a.inline = true;
            }

            flatpickr(e, a);
        }
    });
}