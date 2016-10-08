/*
 *
 */
 
function evaluate(expression, vars) {

    for(k in vars) {
        eval(k+' = '+vars[k])
    }

    eval(expression);
}
