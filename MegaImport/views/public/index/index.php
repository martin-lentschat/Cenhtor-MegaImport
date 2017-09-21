<?php

$head = array('title' => __('Importer des données dans Cenhtor'));

echo head($head);
?>

<?php echo flash();?>

<link rel="stylesheet" href="https://rawgit.com/enyo/dropzone/master/dist/dropzone.css">
<script src="http://cenhtor.msh-lorraine.fr/plugins/MegaImport/dropzone/dropzone.js"></script>
<script type="text/javascript">
    Dropzone.options.myAwesomeDropzone = {
        parallelUploads : 1,
        maxFilesize : 1000, //MB
        acceptedFiles : ".zip",
        dictDefaultMessage : "Glissez et déposez votre fichier ici pour le sauvegarder",
        dictFallbackMessage : "Votre navigateur ne supporte pas le drag'n'drop",
        dictFallbackText : "Veuillez utiliser le formulaire ci-dessous pour uploader vos fichiers :",
        dictFileTooBig : "Le fichier est trop gros : ({{filesize}}Mb). Taille maximum : {{maxFilesize}}Mb",
        dictInvalidFileType : "Vous ne pouvez uploader des fichiers de ce type",
        dictResponseError : "Le serveur a répondu : {{statusCode}}",
        dictCancelUpload : "Upload annulé",
        dictCancelUploadConfirmation : "Êtes vous certain de vouloir annuler cet upload",
        dictRemoveFile : "Fichier supprimé",
        dictMaxFilesExceeded : "vous ne pouvez pas ajouter plus de fichiers",
        //timeout : 10,
        init: function() {
            this.on("success", function(file, response) {
                console.log(response);
                var ifrm = document.createElement("iframe");
                ifrm.setAttribute("src", "nakala-import");
                ifrm.setAttribute("id", "ifrm");
                ifrm.setAttribute("frameBorder", "0");
                ifrm.style.width = "100%";
                ifrm.style.height = "500px";
                var linkCss = document.createElement("link");
                linkCss.setAttribute("rel", "stylesheet");
                linkCss.setAttribute("type", "text/css");
                linkCss.setAttribute("href", "http://cenhtor.msh-lorraine.fr/plugins/MegaImport/views/css/style.css");
                document.getElementById("seven_columns_alpha").appendChild(ifrm);
                jQuery('#ifrm').load( function() {
                    jQuery('#ifrm').contents().find("head").append(jQuery(linkCss));
                });
                document.getElementById("myAwesomeDropzone").style.display = "none";
                var refre = document.createElement("input");
                refre.setAttribute("type", "button");
                refre.setAttribute("class", "submit big green button");
                refre.setAttribute("value", "Ajouter un autre Fichier");
                refre.setAttribute("style", "text-shadow: 1px 1px 2px rgba(0,0,0,.5);cursor: pointer;line-height: 1.75em;font-family: 'Arvo',serif;background-color: #376973;box-shadow: 1px 1px 3px;border: 1px solid #d4d4d4;color: #ddd;");
                refre.setAttribute("onclick", "window.location.reload()");
                document.getElementById("seven_columns_alpha").appendChild(refre);
            });
            this.on("addedfile", function(file) { 
                console.log("Added file");
                document.getElementsByClassName("dz-image")[0].style.zIndex = "0";
                document.getElementsByClassName("dz-details")[0].style.zIndex = "0";
                document.getElementsByClassName("dz-progress")[0].style.zIndex = "0";
            });
            this.on("error", function(errorMessage) {
                console.log(errorMessage);
            });
            this.on("uploadprogress", function(file, progress, byte) {
                if (progress == 100) {
                    console.log("processing");
                    var dzProcess = document.createElement("div");
                    var msg = document.createElement("h4");
                    dzProcess.setAttribute("style", "position: absolute; top: 0;");
                    var img = document.createElement("img");
                    img.setAttribute("src", "/plugins/MegaImport/file/process.gif");
                    img.setAttribute("height", "120px");
                    img.setAttribute("width", "120px");
                    msg.innerHTML = "processing ...";
                    dzProcess.appendChild(img);
                    dzProcess.appendChild(msg);
                    document.getElementsByClassName("dz-progress")[0].style.zIndex = "-1";
                    document.getElementsByClassName("dz-preview")[0].appendChild(dzProcess);
                    //console.log("plop");
                }
                console.log(progress);
            });
        },
    };

</script>
<section class="seven columns alpha" style="width: 45%; float: left; margin: 2.5%">
    <h3>
        Déposer votre fichier de données au format ZIP dans l'utilitaire ci-contre.<br>
    </h3>
    <p>
        N'oubliez pas :
        <li>
            que la taille de ce fichier ZIP doit être inférieure à 1 Go
        </li>
        <li>
            que celui-ci doit contenir un fichier CSV reprenant les métadonnées du ou des fichier(s) joint(s)
        </li>
        <li>
            que vous pouvez modifier les métadonnées de fichier(s) en joignant uniquement le CSV
        </li>
    </p>
    <h4>
        Pensez à faire attention aux fautes de frappe.
    </h4>        
    <p>
        Vous pouvez télécharger <a target="_blank" href="/plugins/MegaImport/file/Cenhtor_basic_CSV.csv">ici</a> un fichier de métadonnées CSV minimales. Vous pouvez ajouter autant de nouvelles métadonnées que désirées entre les colonnes <i>description</i> et <i>projet</i>. Si vous désirez indiquer de multiples valeurs pour une même métadonnée, vous pouvez multiplier les colonnes ou séparer celles-ci par une virgule '<i>,</i>'.<br>
        Pour plus d'informations sur les métadonnées utilisables vous pouvez consulter le site du <a target="_blank" href="http://dublincore.org/documents/dcmi-terms/">DCMI Metadata Terms</a>.<br>
    </p>
    <p>
        Nous vous recommandons de le modifier uniquement à partir de logiciels libres, tel que Libre Office, Open Office, ou Notepad++.
    </p>
    <p>
        Word (et d'autres logiciels) utilisant des formats propriétaires, la conservation de la structure des métadonnées n'est alors pas garantie.
    </p>
</section>

<section id="seven_columns_alpha" class="seven columns alpha" style="width: 45%; float: right; margin: 2.5%;">
	<form action="mega-import/index/mega-import-serveur" class="dropzone" id="myAwesomeDropzone" enctype="multipart/form-data" style="height: 200px;">
		<div class="fallback">
    		<input name="file" type="file" multiple />
  		</div>
	</form>

</section>

</html>
<?php echo foot(); ?>