<!-- when add, assembly item formula modal -->
<div class="modal fade" id="assembly_item_formula_modal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">INTERVIEW FORMULA</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <div class="form-group row">
                    <div class="col-sm-12 text-center">
                        <div class="operators-container">
                            <span data-value="+" class="add_operators">+</span>
                            <span data-value="-" class="add_operators">-</span>
                            <span data-value="/" class="add_operators">/</span>
                            <span data-value="*" class="add_operators">*</span>
                            <span data-value="=" class="add_operators">=</span>
                            <span data-value="<>" class="add_operators">
                                <>
                            </span>
                            <span data-value="<=" class="add_operators">
                                <=< /span>
                                    <span data-value=">=" class="add_operators">>=</span>
                                    <span data-value="<" class="add_operators">
                                        << /span>
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
                        <button type="button" class="btn btn-sm btn-link open_new_question_modal" data-toggle="modal"
                            data-target="#create_new_question_modal" title="Create a question">
                            <img src="{{ asset('icons/create_question.png') }}" class="new-question-icon"
                                alt="create new question">
                        </button>
                        <button type="button" class="btn btn-sm btn-link open_save_formula_modal"
                            title="Save as predefined calculation">
                            <img src="{{ asset('icons/save_formula.png') }}" class="save-formula-icon"
                                alt="save formula">
                        </button>
                    </div>
                    <div class="col-sm-9 my-auto">
                        <div class="add-tag-container">

                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                        id="open_assembly_item_formula_test_modal">
                    </button>
                    <div class="col-sm-1 my-auto px-0 ml-2">
                        <i class="fa fa-trash remove-tags unable" title="Clear"></i>
                    </div>
                </div>

                <div class="form-group row justify-content-around">
                    <div class="col-sm-4 my-auto text-center">
                        <label for="variables">Questions: </label>
                        <select name="variables" class="form-control select2-variables" id="variables">
                            @foreach ($question as $variable)
                                <option value="{{ $variable->id }}" data-question_type="{{ $variable->type }}"
                                    data-note="{{ $variable->notes }}">
                                    {{ $variable->question }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-4 my-auto text-center">
                        <label for="pre_defined_calc">Pre-defined calculations: </label>
                        <select name="pre_defined_calc" class="form-control select2-pre-defined-calc"
                            id="pre_defined_calc">
                            @foreach ($pre_defined_calculations as $calc)
                                <option value="{{ $calc->id }}" data-formula_body="{{ $calc->formula_body }}">
                                    {{ $calc->calculation_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="a_clear_formula">Clear Formula</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="a_save_assembly_item_formula" data-id=""
                    data-page_number="">
                    Save
                </button>
            </div>

        </div>
    </div>
</div>
