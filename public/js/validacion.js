function validar(requerido){
    $("."+requerido).each(function(){
        input = $(this);
        bool = $.trim( input.val() ) !== "";
        return bool;
    })
    
    return bool;
}