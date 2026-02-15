<div class="barraNavegacio">
</div>

<h2>Blog</h2>
<h4 id="titolBlog"></h4>

<div class="alert alert-success" id="okMessage" style="display:none">
    <div id="okText"></div>
</div>
<div class="alert alert-danger" id="errMessage" style="display:none">
    <div id="errText"></div>
</div>

<form action="" id="modificaBlog" class="form">
    <input type="hidden" id="id" name="id" value="">
    <input type="hidden" id="post_date" name="post_date" value="">
    <input type="hidden" id="post_modified" name="post_modified" value="">

    <div class="row g-3">

        <!-- post_type -->
        <div class="col-12 col-lg-3">
            <label class="form-label" for="post_type">Tipus article</label>
            <select class="form-select" id="post_type" name="post_type">
                <!-- TS omple opcions -->
            </select>
        </div>

        <!-- post_status -->
        <div class="col-12 col-lg-3">
            <label class="form-label" for="post_status">Estat</label>
            <select class="form-select" id="post_status" name="post_status">
                <!-- TS omple opcions -->
            </select>
        </div>

        <!-- lang (int(1)) -->
        <div class="col-12 col-lg-3">
            <label class="form-label" for="lang">Idioma</label>
            <select class="form-select" id="lang" name="lang">
                <!-- TS omple opcions -->
            </select>
        </div>

        <!-- categoria (binary(16)) -->
        <div class="col-12 col-lg-3">
            <label class="form-label" for="categoria">Categoria</label>
            <select class="form-select" id="categoria" name="categoria">
                <!-- TS omple opcions -->
            </select>
        </div>

        <!-- post_title -->
        <div class="col-12">
            <label class="form-label" for="post_title"><strong>Títol</strong></label>
            <input class="form-control" type="text" id="post_title" name="post_title" value="" required>
        </div>

        <!-- slug -->
        <div class="col-12 col-lg-6">
            <label class="form-label" for="slug">Slug</label>
            <input class="form-control" type="text" id="slug" name="slug" value="" required>
        </div>

        <!-- post_excerpt -->
        <div class="col-12">
            <label class="form-label" for="post_excerpt"><strong>Extracte</strong></label>
            <textarea class="form-control" id="post_excerpt" name="post_excerpt" rows="3"></textarea>
        </div>

        <!-- post_content (Trix) -->
        <div class="col-12">
            <label class="form-label"><strong>Article</strong></label>
            <input type="hidden" id="post_content" name="post_content" value="">
            <trix-editor input="post_content" class="editor-blog"></trix-editor>
        </div>

        <!-- Accions -->
        <div class="col-12 mt-3">
            <div class="d-flex justify-content-between">
                <a href="#" onclick="window.history.back(); return false;" class="btn btn-secondary">Tornar enrere</a>
                <div class="d-flex gap-2">
                    <button id="btnSave" class="btn btn-primary" type="submit">Desar</button>
                </div>
            </div>
        </div>

    </div>
</form>

<style>
    .editor-blog {
        min-height: 500px;
        padding: 1rem;
        font-size: 1.1rem;
        line-height: 1.6;
    }
</style>