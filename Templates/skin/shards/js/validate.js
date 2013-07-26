$(document).ready(function(){


    $("#form-login").validate({
      
      onfocusout: false,
      onkeyup: false,
      onclick: false,
      focusInvalid: false,
      focusCleanup: false,


      errorPlacement: function(error, element) {
           $(".message-text .corners-content").html(error);
         },
         debug:true,

        // errorLabelContainer: "#vall",

       rules:{

            login:{
                required: true,
                email : "email",
            },

            password:{
                required: true,
            },
       },

       messages:{

            login:{
                required: "Поле логин обязательно для заполнения",
                email : "Email не валидный",
            },

            password:{
                required: "Поле пароль обязательно для заполнения",
            },

       }

    });

});

