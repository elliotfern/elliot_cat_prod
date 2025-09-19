<div class="barraNavegacio"></div>

<div class="container-fluid form">

    <h2>Gestió comptabilitat i clients</h2>
    <div id="titolForm"></div>

    <div class="alert alert-success" id="okMessage" style="display:none" role="alert">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger" id="errMessage" style="display:none" role="alert">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" class="row g-3" id="formFacturaClient" data-success-redirect-template="/gestio/base-dades-persones/fitxa-persona/{slug}">

        <div class="row g-3">
            <input type="hidden" id="id" name="id" />

            <div class="col-md-4">
                <label for="idUser">Company:</label>
                <select class="form-select" name="idUser" id="idUser">
                </select>
            </div>

            <div class="col-md-4">
                <label for="facConcepte">Invoice concept</label>
                <input class="form-control" type="text" name="facConcepte" id="facConcepte" />
                <label style="color:#dc3545;display:none" id="AutNomCheck">* Invalid data</label>
            </div>

            <div class="col-md-4">
                <label for="facData">Invoice date:</label>
                <input class="form-control" type="date" name="facData" id="facData" />
                <label style="color:#dc3545;display:none" id="AutCognom1Check">* Missing data</label>
            </div>

            <div class="col-md-4">
                <label for="facDueDate">Due Date:</label>
                <input class="form-control" type="date" name="facDueDate" id="facDueDate" />
                <label style="color:#dc3545;display:none" id="AutCognom1Check">* Missing data</label>
            </div>

            <div class="col-md-4">
                <label for="facSubtotal">Invoice Subtotal (without VAT):</label>
                <input class="form-control" type="url" name="facSubtotal" id="facSubtotal" />
                <label style="color:#dc3545;display:none" id="AutWikipediaCheck">* Missing data</label>
            </div>

            <div class="col-md-4">
                <label for="facFees">Fees (STRIPE):</label>
                <input class="form-control" type="url" name="facFees" id="facFees" />
                <label style="color:#dc3545;display:none" id="AutWikipediaCheck">* Missing data</label>
            </div>

            <div class="col-md-4">
                <label for="facTotal">Invoice total:</label>
                <input class="form-control" type="url" name="facTotal" id="facTotal" />
                <label style="color:#dc3545;display:none" id="AutWikipediaCheck">* Missing data</label>
            </div>

            <div class="col-md-4">
                <label for="facVAT">VAT amount:</label>
                <input class="form-control" type="url" name="facVAT" id="facVAT" />
                <label style="color:#dc3545;display:none" id="AutWikipediaCheck">* Missing data</label>
            </div>

            <div class="col-md-4">
                <label for="facIva">Vat type:</label>
                <select class="form-select" name="facIva" id="facIva">
                </select>
            </div>

            <div class="col-md-4">
                <label for="facPaymentType">Payment method:</label>
                <select class="form-select" name="facPaymentType" id="facPaymentType">
                </select>
            </div>

            <div class="col-md-4">
                <label for="facEstat">Invoice status:</label>
                <select class="form-select" name="facEstat" id="facEstat">
                </select>
            </div>

            <div class="container" style="margin-top:25px">
                <div class="row">
                    <div class="col-6 text-left">

                    </div>
                    <div class="col-6 text-right derecha">
                        <button type="submit" class="btn btn-primary" id="btnFactura">Introduir dades</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>