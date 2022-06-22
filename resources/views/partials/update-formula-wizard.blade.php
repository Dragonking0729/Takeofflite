<div class="form-group row">
    <div class="col-sm-12 text-center">
        <div class="operators-container">
            <span data-value="+" class="operators">+</span>
            <span data-value="-" class="operators">-</span>
            <span data-value="/" class="operators">/</span>
            <span data-value="*" class="operators">*</span>
            <span data-value="=" class="operators">=</span>
            <span data-value="<>" class="operators"><></span>
            <span data-value="<=" class="operators"><=</span>
            <span data-value=">=" class="operators">>=</span>
            <span data-value="<" class="operators"><</span>
            <span data-value=">" class="operators">></span>
            <span data-value="IF" class="operators">IF</span>
            <span data-value="AND" class="operators">AND</span>
            <span data-value="OR" class="operators">OR</span>
            <span data-value="(" class="operators">(</span>
            <span data-value=")" class="operators">)</span>
            <span data-value="," class="operators">,</span>
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
        <div class="tag-container">
            @if($formula_params)
                @foreach($formula_params as $item)
                    <span class="cursor-input" data-item="{{$loop->index}}"></span>
                    <div class="tag {{$item['type']}}" data-item="{{$loop->index}}">
                        {{$item['val']}}
                    </div>
                @endforeach
                <span class="cursor-input current" data-item="{{count($formula_params)}}">
                    <input type="text" size="1" autofocus/>
                </span>
            @else
                <span class="cursor-input current" data-item="0">
                    <input type="text" size="1" autofocus/>
                </span>
            @endif
        </div>
    </div>
    <button type="button" class="btn btn-sm btn-outline-secondary mr-1" id="test_formula">
    </button>
    <button type="button" class="btn btn-sm btn-outline-secondary mr-1" id="clear_formula">
        Clear Formula
    </button>
    <i class="fa fa-trash my-auto remove-tags unable" title="Clear"></i>
</div>

<div class="form-group row justify-content-around">
    <div class="col-sm-3 my-auto text-center">
        <label for="variables">Questions: </label>
        <select name="variables" class="form-control select2-variables" id="variables">
            @foreach($question as $variable)
                <option value="{{$variable->id}}" data-question_type="{{$variable->type}}"
                        data-note="{{$variable->notes}}">
                    {{$variable->question}}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-sm-3 my-auto text-center">
        <label for="functions">Functions: </label>
        <select name="functions" class="form-control select2-functions" id="functions">
            <option value="sqrt">SQRT</option>
        </select>
    </div>

    <div class="col-sm-3 my-auto text-center">
        <label for="pre_defined_calc">Pre-defined calculations: </label>
        <select name="pre_defined_calc" class="form-control select2-pre-defined-calc"
                id="pre_defined_calc">
            @foreach($pre_defined_calculations as $calc)
                <option value="{{$calc->id}}" data-formula_body="{{$calc->formula_body}}">
                    {{$calc->calculation_name}}
                </option>
            @endforeach
        </select>
    </div>
</div>