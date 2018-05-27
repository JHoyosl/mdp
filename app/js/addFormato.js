define(['api','jquery','ko','moment','validate','boostrapDatePicker','bootstrap','ui','moment/locale/es','smartWizard'], function(api,$,ko,moment){
    
    return {
		
		addFormatoModel: function(){
			
			//forma variables
			self.formatoId = ko.observable().extend({ uppercase: true });
			self.formatoDescripcion = ko.observable().extend({ uppercase: true });
			self.fileName = ko.observable();
			
			//optionsvars
			self.bancoList = ko.observableArray();
			self.selectedBanco = ko.observable();
			
			
			self.loadMaestros = function(){
				
				api.ajaxCom("bancos","getBancoListSelect",{},function(response){
					
					var tmpOpt = response.data.message;
					var opciones = [];
					
					for(var i = 0; i<tmpOpt.length; i++){
						
						opciones.push(new api.OptList(tmpOpt[i]["ID"],tmpOpt[i]["DESCRIPCION"]));
						
					}
					self.bancoList(opciones);
						
				});
				
			} 
			
			////// PASOS SUBMIT /////
			
			self.fileSelected = function(){
				
				if($("#formatoFileUpload")[0].files.length > 0){
					
					var fileTmp =  $("#formatoFileUpload")[0].files[0];
					var extension  = fileTmp.name.substr( (fileTmp.name.lastIndexOf('.') +1) );
					var extArray = ["xlsx"];
					

					if(extArray.includes(extension)){

						self.fileName(fileTmp.name);
						
						var form = new FormData();
						$.each($('#formatoFileUpload')[0].files, function(i, file) {
							form.append(i, file);
						});
						
						api.uploadAjax("bancos","segmentarFormato",{}, form, function(response){
							
							console.log(response);
							
						});

					}else{

						alert("Extensión no permitida");
					}
				}
				
			}
			
			self.paso1Submit = function(){
				
				
				
				
			}
			
			
			///SAMRT WIZARDS ////////
			self.leaveAStepCallback = function(obj){
				
				console.log("leaveAStepCallback");
				var step_num= obj.attr('rel');
				return validateSteps(step_num);
			}
			
			self.validateSteps = function(step){
				
				
				var isStepValid = true;
			  // validate step 1
				if(step == 1){
					$("#submitPaso1").trigger( "click" );
					console.log("validateSteps - paso 1");
					// $("#paso1Form").trigger("submit");
					// if(true){//validateStep1() == false ){
					  // isStepValid = false; 
					  // $('#wizard').smartWizard('showMessage','Please correct the errors in step'+step+ ' and click next.');
					  // $('#wizard').smartWizard('setError',{stepnum:step,iserror:true});         
					// }else{
					  // $('#wizard').smartWizard('hideMessage');
					  // $('#wizard').smartWizard('setError',{stepnum:step,iserror:false});
					// }
				}
				
				
				return isStepValid;
			}
			
			self.onFinishCallback = function(){
				
				if(validateAllSteps()){
				// $('form').submit();
				}
			}
			$('#wizard').smartWizard(
				{
					onLeaveStep:leaveAStepCallback,
					// onFinish:onFinishCallback,
					enableFinishButton: false,
					enableFinishButton:true
				}
			);
			///SAMRT WIZARDS END ////////
			
		},
		initialize: function(){

        	
			
			function leaveAStepCallback(obj){
				
				console.log("leaveAStepCallback");
				var step_num= obj.attr('rel');
				return validateSteps(step_num);
			  }
			  
			  function onFinishCallback(){
			   if(validateAllSteps()){
				$('form').submit();
			   }
			  }
	
			
        	ko.applyBindings(new this.addFormatoModel(), document.getElementById("addFormatoModel"));   
			
			$('.buttonNext').addClass('btn btn-success');
			$('.buttonPrevious').addClass('btn btn-primary');
			$('.buttonFinish').addClass('btn btn-default');
			// $('.stepContainer').height('auto');
			
			$('#formatoFieldUpload').click(function(){
				
				$("#formatoFileUpload").trigger( "click" );
				
				
			})
			
			// self.loadEmpresaTable();
			self.loadMaestros();
			//$('#fechaNac').datetimepicker({format: 'YYYY-MM-DD',locale: 'ru'});
        },
	}
	
	
	
})