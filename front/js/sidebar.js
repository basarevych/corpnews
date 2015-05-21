/*
    Makes sidebar fixed or static positioned
*/
function updateSidebar()
{
    var win = $(window);
    $('[data-sidebar]').each(function (index, element) {
        element = $(element);
        var edge = element.attr('data-sidebar');
        var sizes = ['xs', 'sm', 'md', 'lg'];

        element.css({ position: 'fixed' });

        var bottom = element.position().top + element.outerHeight(true),
            position = win.height() < bottom ? 'static' : undefined;

        if (typeof position == 'undefined') {
            var test = $('<div>'), current;
            test.appendTo($('body'));

            for (var i = sizes.length - 1; i >= 0; i--) {
                test.addClass('hidden-' + sizes[i]);
                if (test.is(':hidden')) {
                    current = sizes[i];
                    break;
                }
            };
            test.remove();

            if (typeof current != 'undefined')
                position = sizes.indexOf(edge) > sizes.indexOf(current) ? 'static' : 'fixed';
        }

        if (typeof position != 'undefined')
            element.css({ position: position });

        element.css({ width: element.parent().width() });
    });
}

$(document).ready(function () {
    $(window).on('resize', updateSidebar);
    updateSidebar();
});
