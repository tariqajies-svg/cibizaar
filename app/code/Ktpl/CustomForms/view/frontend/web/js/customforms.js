define([
    "jquery"
],function($) {
    "use strict";
    return function(config) 
    {
    
        $('#taxexempt').on('change', function(){
            var taxexempt = $(this).val(); 
            if(taxexempt=='Yes')
            {
                $("#hidden_taxupload").show();
            }else{
                $("#hidden_taxupload").hide();
            }
        });

        $('#reason-return').on('change', function(){
			var taxexempt = $(this).val(); 
			if(taxexempt=='Arrived Damaged')
			{
				$("#hidden_upload").show();
			}else{
				$("#hidden_upload").hide();
			}
		});
        
        function readFile(input) {
        
            var files = input.files,
            filesLength = files.length;
            $("p.pip-message").remove();
            $("p.tax-pip-message").remove();
            $(".submit.primary").removeAttr('disabled');
            $("span.pip").remove();

            for (var i = 0; i < filesLength; i++) {
                
                (function(file) {
                    var f = files[i]
                    var type = f.type;
                    var fileTypes = ['jpg', 'jpeg', 'png', 'gif', 'tif', 'pdf', 'docx','doc'];  //acceptable file types
                    var extension = input.files[i].name.split('.').pop().toLowerCase(),  //file extension from input file
                    isCorrectFormat = fileTypes.indexOf(extension) > -1;  //is extension in acceptable types
                    if(isCorrectFormat)
                    {
                        var name = f.name;
                        var fileReader = new FileReader();
                        fileReader.onload = (function(e) {
                            
                            var src = config.doc_img;

                            if(type == "application/pdf") 
                            {
                                src = config.pdf_img;

                            }else if(type.includes("image/")){

                                src = e.target.result;
                            }
                            
                            $("<span class=\"pip\">" +
                                    "<img class=\"imageThumb\" src=\"" + src + "\" title=\"" + name + "\"/>" +
                                    "<br/><span class=\"remove\">x</span>" +
                                    "</span>").insertAfter(".field.attachment");

                            $(".remove").click(function(){
                                $(this).parent(".pip").remove();
                            });
                        
                        });

                        fileReader.readAsDataURL(f);
                    }else{
                        $(".pip-message").remove();
                        $("p.tax-pip-message").remove();
                        $(".submit.primary").prop("disabled", true);

                        $("<p class=\"pip-message\" style=\"color:red;\">" + 'Disallowed file type. Please upload one of the following: jpg, jpeg, png, gif, tif, pdf, doc, docx' +
                                    "</p>").insertAfter(".field.attachment");
                    }
                
                })(files[i]);
            }
        }

        function readTaxFile(input) {
        
            var files = input.files,
            filesLength = files.length;
            $("p.tax-pip-message").remove();
            $(".pip-message").remove();
            $(".submit.primary").removeAttr('disabled');
            $("span.tax-pip").remove();

            for (var i = 0; i < filesLength; i++) {
                
                (function(file) {
                    var f = files[i]
                    var type = f.type;
                    var fileTypes = ['jpg', 'jpeg', 'png', 'gif', 'tif', 'pdf', 'docx','doc'];  //acceptable file types
                    var extension = input.files[i].name.split('.').pop().toLowerCase(),  //file extension from input file
                    isCorrectFormat = fileTypes.indexOf(extension) > -1;  //is extension in acceptable types
                    if(isCorrectFormat)
                    {
                        var name = f.name;
                        var fileReader = new FileReader();
                        fileReader.onload = (function(e) {
                            
                            var src = config.doc_img;

                            if(type == "application/pdf") 
                            {
                                src = config.pdf_img;

                            }else if(type.includes("image/")){

                                src = e.target.result;
                            }
                            
                            $("<span class=\"tax-pip\">" +
                                    "<img class=\"tax-imageThumb\" src=\"" + src + "\" title=\"" + name + "\"/>" +
                                    "<br/><span class=\"tax-remove\">x</span>" +
                                    "</span>").insertAfter(".field.tax-attachment");

                            $(".tax-remove").click(function(){
                                $(this).parent(".tax-pip").remove();
                            });
                        
                        });

                        fileReader.readAsDataURL(f);
                    }else{
                        $(".tax-pip-message").remove();
                        $(".pip-message").remove();
                        $(".submit.primary").prop("disabled", true);

                        $("<p class=\"tax-pip-message\" style=\"color:red;\">" + 'Disallowed file type. Please upload one of the following: jpg, jpeg, png, gif, tif, pdf, doc, docx' +
                                    "</p>").insertAfter(".field.tax-attachment");
                    }
                
                })(files[i]);
            }
        }

        $(".dropzone").change(function() {
        readFile(this);
        });

        $(".tax-dropzone").change(function() {
        readTaxFile(this);
        });

        $('.dropzone-wrapper .tax-dropzone-wrapper').on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        });

        $('.dropzone-wrapper .tax-dropzone-wrapper').on('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
        });
        
    };
});