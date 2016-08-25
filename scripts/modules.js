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
        return fs.statSync(filePath).isFile();
    }
    catch (err) {
        return false;
    }
}

function getModules(exclude) {
    
    if (! Array.isArray(exclude)) {
        exclude = []
    }
    
    const directories = getDirectories(path.resolve(process.cwd(), 'modules'))
    let modules = {}
    
    directories.forEach(directory => {
        const subDirectories = getDirectories('modules/' + directory)
        
        subDirectories.forEach(subDirectory => modules[subDirectory] = path.join(directory, subDirectory))
    })
    
    // Build full path
    for (let mod in modules) {
        if (modules.hasOwnProperty(mod)) {
            
            // Prevent compiling of resources in main modules directory
            // or when it's manually excluded
            if (modules[mod] === 'sebastiaanluca/modules' || exclude.indexOf(modules[mod]) !== - 1) {
                
                console.info(`[NOTICE] Excluding module "${modules[mod]}" from build process`)
                
                delete modules[mod]
                continue
            }
            
            // Concatenate the path to the module resource entry file
            modules[mod] = './' + path.join(modules[mod], 'resources/scripts/' + mod + '.js')
            
            // Remove module if entry file does not exist
            if (! fileExists('modules/' + modules[mod])) {
                console.info(`[NOTICE] Module "${mod}" does not have an entry file located at ${modules[mod]}`)
                delete modules[mod]
            }
        }
    }
    
    return modules
}

//

const modules = getModules

module.exports = modules