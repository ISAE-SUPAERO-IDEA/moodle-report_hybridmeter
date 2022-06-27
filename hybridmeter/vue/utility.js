function buildStringsArgument(keys, component) {
    let output = [];
    keys.forEach(element => {
        output.push({
            key: element,
            component: component
        })
    });
    return output;
}

export { buildStringsArgument };