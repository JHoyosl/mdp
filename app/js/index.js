 
define(['api','jquery','ko'], function(api,$,ko){
    
    return {
        

        //Se crea un modelo para control

        menusViewModel: function(){


            self.usaurioNombre = ko.observable();
        	self.logout = function(){
				
	        	api.ajaxCom("user","logout",{},function(response){
                
					$resp = api.chkLogin();

				},false);
	        };

            self.loadHome = function () {
                api.target("home");
                 
            };
            self.loadUsuarioView = function () {
                api.target("usuarios");
                 
            };
			self.loadBancosView = function () {
                api.target("bancos");
                 
            };
            self.loadEmpresaView = function () {
                api.target("empresas");
                 
            };
			self.loadAddFormatoView = function () {
                api.target("addFormato");
                 
            };


        },

        loginViewModel: function(){

        	self.user = ko.observable("admingsys");
            self.pssw = ko.observable("12345");
            
            self.login = function(){

            	var info = {};

            	info.user = self.user();
            	info.pssw = self.pssw();

            	if(info.user == ""){

            		alert("Debe ingresar un usuario");
            		return;
            	}
            	if(info.pssw == ""){

            		alert("Debe ingresar una contraseña");
            		return;
            	}
            	
	        	api.ajaxCom("user","login",info,function(response){


					if(response.data.status){
						
						self.user("");	
						self.pssw("");	
						api.chkLogin();

					}else{
						
						alert("Usuario y/o Contraseña Incorrectos");
						
						
					}
				},false);

	        }; 

	        
        },
        initialize: function(){
			
			console.log("hola");
        	ko.applyBindings(new this.menusViewModel(), document.getElementById("menusWrapper"));        	
        	ko.applyBindings(new this.loginViewModel(), document.getElementById("login-box"));        	
        	
			api.chkLogin();

			// ko.cleanNode(document.getElementById("login-box"));
        },
        


    }


});