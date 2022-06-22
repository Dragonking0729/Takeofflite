<div class="form-group row">
    <div class="col-sm-12 text-center">
        <div class="operators-container">
            <span data-value="+" class="add_operators">+</span>
            <span data-value="-" class="add_operators">-</span>
            <span data-value="/" class="add_operators">/</span>
            <span data-value="*" class="add_operators">*</span>
            <span data-value="=" class="add_operators">=</span>
            <span data-value="<>" class="add_operators"><></span>
            <span data-value="<=" class="add_operators"><=</span>
            <span data-value=">=" class="add_operators">>=</span>
            <span data-value="<" class="add_operators"><</span>
            <span data-value=">" class="add_operators">></span>
            <span data-value="IF" class="add_operators">IF</span>
            <span data-value="AND" class="add_operators">AND</span>
            <span data-value="OR" class="add_operators">OR</span>
            <span data-value="(" class="add_operators">(</span>
            <span data-value=")" class="add_operators">)</span>
            <span data-value="," class="add_operators">,</span>
        </div>
    </div>
</div>

<div class="form-group row">
    <div class="col-sm-1 d-flex my-auto text-center">
        <button type="button" class="btn btn-sm btn-link open_new_question_modal"
                data-toggle="modal" data-target="#create_new_question_modal"
                title="Create a question">
            <img src="{{asset('icons/create_question.png')}}" class="new-question-icon"
                 alt="create new question">
        </button>
        <button type="button" class="btn btn-sm btn-link open_save_formula_modal"
                title="Save as predefined calculation">
            <img src="{{asset('icons/save_formula.png')}}" class="save-formula-icon"
                 alt="save formula">
        </button>
    </div>
    <div class="col-sm-9 my-auto">
        <div class="add-tag-container">
            <span class="new-input add-current" data-item="0">
                <input type="text" size="1" autofocus/>
            </span>
        </div>
    </div>
    <button type="button" class="btn btn-sm btn-outline-secondary mr-1" id="test_formula_add">
    </button>
    <button type="button" class="btn btn-sm btn-outline-secondary mr-1" id="clear_formula_add">
        Clear Formula
    </button>
    <i class="fa fa-trash my-auto add-remove-tags" title="Clear"></i>
</div>

<div class="form-group row justify-content-around">
    <div class="col-sm-3 my-auto text-center">
        <label for="add_variables">Questions: </label>
        <select name="add_variables" class="form-control select2-variables" id="add_variables">
            @foreach($question as $variable)
                <option value="{{$variable->id}}" data-question_type="{{$variable->type}}"
                        data-note="{{$variable->notes}}">
                    {{$variable->question}}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-sm-3 my-auto text-center">
        <label for="add_functions">Functions: </label>
        <select name="add_functions" class="form-control select2-functions" id="add_functions">
            <option value="sqrt">SQRT</option>
        </select>
    </div>

    <div class="col-sm-3 my-auto text-center">
        <label for="add_pre_defined_calc">Pre-defined calculations: </label>
        <select name="add_pre_defined_calc" class="form-control select2-pre-defined-calc"
                id="add_pre_defined_calc">
            @foreach($pre_defined_calculations as $calc)
                <option value="{{$calc->id}}" data-formula_body="{{$calc->formula_body}}">
                    {{$calc->calculation_name}}
                </option>
            @endforeach
        </select>
    </div>
</div>