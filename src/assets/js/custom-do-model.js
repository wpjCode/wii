$(function () {

    // hide message category when I18N is disabled
    $('form #generator-iscachemodel').change(function () {
        $('form .field-generator-dodbmodel').toggle($(this).is(':checked'));
    }).change();

    $('.default-view-files').show();
    $('.default-view-results').show();
    $('button[name="generate"]').show();
});
