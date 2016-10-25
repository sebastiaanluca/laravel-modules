/**
 * Loads the modules found in /modules and prepares them to be merged with the webpack config.
 */

'use strict'

const fs = require('fs')
const path = require('path')

//

function getDirectories(srcpath) {
    return fs.readdirSync(srcpath).filter(file => fs.statSync(path.resolve(srcpath, file)).isDirectory())
}

function fileExists(filePath) {
    try {
        return fs.statSync(path.resolve('./modules', filePath)).isFile()
    }
    catch (err) {
        return false
    }
}

function getModules(exclude) {
    if (!Array.isArray(exclude)) {
        exclude = []
    }
    
    // Get all modules in the root directory of /modules
    let modules = getDirectories(path.resolve(process.cwd(), 'modules'))
    
    // Build full path
    let buildModules = []
    
    for (const module of modules) {
        // Prevent compiling of resources in main modules directory or when it's manually excluded
        if (module === 'sebastiaanluca/modules' || exclude.indexOf(module) !== -1) {
            console.info(`[NOTICE] Excluding module "${module}" from build process`)
            continue
        }
        
        // Concatenate the path to the module resource entry file
        const entryScript = `./${module}/resources/scripts/main.js`
        
        // Verify entry script existence
        if (!fileExists(entryScript)) {
            console.info(`[NOTICE] Module "${module}" does not have an entry file located at ${entryScript}`)
            continue
        }
        
        buildModules.push(entryScript)
    }
    
    return buildModules
}

//

const modules = getModules

module.exports = modules