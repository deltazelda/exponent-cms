<h2>User Input Fields</h2>
You may define fields here that the user is required to fill out when purchasing this product.  For instance, to supply a value to be imprinted on an item.{br}
{control class="userInputToggle" type="checkbox" name="user_input_use[0]"  label="Show User Field 1" value=1 checked=$record->user_input_fields.0.use}
<div>
    <table>
        <tr>
            <td>
                {control type="text" name="user_input_name[0]" label="Field Name" value=$record->user_input_fields.0.name}    
            </td>
            <td>
                {control type="checkbox" name="user_input_is_required[0]" label="Required?" value=1 checked=$record->user_input_fields.0.is_required}
            </td>
        </tr>
        <tr>
            <td>
                {control type="text" name="user_input_min_length[0]" label="Minimum Length" value=$record->user_input_fields.0.min_length}    
            </td>
            <td>
                {control type="text" name="user_input_max_length[0]" label="Maximum Length" value=$record->user_input_fields.0.max_length}
            </td>
        </tr>
        <tr>
            <td colspan=2>
                {control type="textarea" name="user_input_description[0]" label="Description For Users" height=200 value=$record->user_input_fields.0.description}    
            </td> 
        </tr>
    </table>
    <hr>
</div>
{control class="userInputToggle" type="checkbox" name="user_input_use[1]"  label="Show User Field 2" value=1 checked=$record->user_input_fields.1.use}
<div>
    <table>
        <tr>
            <td>
                {control type="text" name="user_input_name[1]" label="Field Name" value=$record->user_input_fields.1.name}    
            </td>
            <td>
                {control type="checkbox" name="user_input_is_required[1]" label="Required?" value=1 checked=$record->user_input_fields.1.is_required}
            </td>
        </tr>
        <tr>
            <td>
                {control type="text" name="user_input_min_length[1]" label="Minimum Length" value=$record->user_input_fields.1.min_length}    
            </td>
            <td>
                {control type="text" name="user_input_max_length[1]" label="Maximum Length" value=$record->user_input_fields.1.max_length}
            </td>
        </tr>
        <tr>
            <td colspan=2>
                {control type="textarea" name="user_input_description[1]" label="Description For Users" height=200 value=$record->user_input_fields.1.description}    
            </td> 
        </tr>
    </table>
    <hr>
</div>
{control class="userInputToggle" type="checkbox" name="user_input_use[2]"  label="Show User Field 3" value=1 checked=$record->user_input_fields.2.use}
<div>
    <table>
        <tr>
            <td>
                {control type="text" name="user_input_name[2]" label="Field Name" value=$record->user_input_fields.2.name}    
            </td>
            <td>
                {control type="checkbox" name="user_input_is_required[2]" label="Required?" value=1 checked=$record->user_input_fields.2.is_required}
            </td>
        </tr>
        <tr>
            <td>
                {control type="text" name="user_input_min_length[2]" label="Minimum Length" value=$record->user_input_fields.2.min_length}    
            </td>
            <td>
                {control type="text" name="user_input_max_length[2]" label="Maximum Length" value=$record->user_input_fields.2.max_length}
            </td>
        </tr>
        <tr>
            <td colspan=2>
                {control type="textarea" name="user_input_description[2]" label="Description For Users" height=200 value=$record->user_input_fields.2.description}    
            </td> 
        </tr>
    </table>
    <hr>
</div>
{control class="userInputToggle" type="checkbox" name="user_input_use[3]"  label="Show User Field 4" value=1 checked=$record->user_input_fields.3.use}
<div>
    <table>
        <tr>
            <td>
                {control type="text" name="user_input_name[3]" label="Field Name" value=$record->user_input_fields.3.name}    
            </td>
            <td>
                {control type="checkbox" name="user_input_is_required[3]" label="Required?" value=1 checked=$record->user_input_fields.3.is_required}
            </td>
        </tr>
        <tr>
            <td>
                {control type="text" name="user_input_min_length[3]" label="Minimum Length" value=$record->user_input_fields.3.min_length}    
            </td>
            <td>
                {control type="text" name="user_input_max_length[3]" label="Maximum Length" value=$record->user_input_fields.3.max_length}
            </td>
        </tr>
        <tr>
            <td colspan=2>
                {control type="textarea" name="user_input_description[3]" label="Description For Users" height=200 value=$record->user_input_fields.3.description}    
            </td> 
        </tr>
    </table>
    <hr>
</div>
{control class="userInputToggle" type="checkbox" name="user_input_use[4]"  label="Show User Field 5" value=1 checked=$record->user_input_fields.4.use}
<div>
    <table>
        <tr>
            <td>
                {control type="text" name="user_input_name[4]" label="Field Name" value=$record->user_input_fields.4.name}    
            </td>
            <td>
                {control type="checkbox" name="user_input_is_required[4]" label="Required?" value=1 checked=$record->user_input_fields.4.is_required}
            </td>
        </tr>
        <tr>
            <td>
                {control type="text" name="user_input_min_length[4]" label="Minimum Length" value=$record->user_input_fields.4.min_length}    
            </td>
            <td>
                {control type="text" name="user_input_max_length[4]" label="Maximum Length" value=$record->user_input_fields.4.max_length}
            </td>
        </tr>
        <tr>
            <td colspan=2>
                {control type="textarea" name="user_input_description[4]" label="Description For Users" height=200 value=$record->user_input_fields.4.description}    
            </td> 
        </tr>
    </table>
    <hr>
</div>
{control class="userInputToggle" type="checkbox" name="user_input_use[5]"  label="Show User Field 6" value=1 checked=$record->user_input_fields.5.use}
<div>
    <table>
        <tr>
            <td>
                {control type="text" name="user_input_name[5]" label="Field Name" value=$record->user_input_fields.5.name}    
            </td>
            <td>
                {control type="checkbox" name="user_input_is_required[5]" label="Required?" value=1 checked=$record->user_input_fields.5.is_required}
            </td>
        </tr>
        <tr>
            <td>
                {control type="text" name="user_input_min_length[5]" label="Minimum Length" value=$record->user_input_fields.5.min_length}    
            </td>
            <td>
                {control type="text" name="user_input_max_length[5]" label="Maximum Length" value=$record->user_input_fields.5.max_length}
            </td>
        </tr>
        <tr>
            <td colspan=2>
                {control type="textarea" name="user_input_description[5]" label="Description For Users" height=200 value=$record->user_input_fields.5.description}    
            </td> 
        </tr>
    </table>
    <hr>
</div>
